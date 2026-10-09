<?php

include "db.php";

$result = $conn->query("

    SELECT
        b.branch_name,
        COALESCE(SUM(CASE WHEN t.transaction_type = 'Sale' THEN t.amount ELSE 0 END), 0) AS sales,
        COALESCE(SUM(CASE WHEN t.transaction_type = 'Purchase' THEN t.amount ELSE 0 END), 0) AS purchases,
        COALESCE(SUM(CASE WHEN t.transaction_type = 'Expense' THEN t.amount ELSE 0 END), 0) AS expenses

    FROM branches b

    LEFT JOIN transactions t
        ON b.branch_id = t.branch_id

    GROUP BY b.branch_id, b.branch_name

    ORDER BY b.branch_name

");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reports | AI Business Intelligence</title>

    <link rel="stylesheet" href="style.css">

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            color: #172033;
        }

        /* SIDEBAR */

        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: linear-gradient(180deg, #111827, #1e293b);
            color: white;
            padding: 25px 18px;
            box-sizing: border-box;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
        }

        .brand {
            padding: 5px 12px 25px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
            margin-bottom: 14px;
        }

        .brand h2 {
            margin: 0;
            font-size: 19px;
            line-height: 1.3;
        }

        .brand p {
            margin: 7px 0 0;
            color: #94a3b8;
            font-size: 11px;
        }

        .nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 14px;
            margin: 5px 0;
            border-radius: 10px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 13px;
            transition: 0.2s;
        }

        .nav a:hover {
            background: rgba(99,102,241,0.18);
            color: white;
        }

        .nav a.active {
            background: linear-gradient(90deg, #6366f1, #4f46e5);
            color: white;
            box-shadow: 0 7px 18px rgba(79,70,229,0.25);
        }

        /* MAIN */

        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding: 32px;
            box-sizing: border-box;
        }

        /* HEADER */

        .page-header {
            background: linear-gradient(135deg, #111827, #4f46e5);
            color: white;
            border-radius: 22px;
            padding: 30px 34px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 15px 35px rgba(30,41,59,0.15);
        }

        .page-header h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .page-header p {
            margin: 0;
            color: #dbeafe;
            font-size: 13px;
        }

        .header-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: rgba(255,255,255,0.14);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }

        /* REPORT CARD */

        .report-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 28px rgba(15,23,42,0.07);
            overflow-x: auto;
        }

        .report-title {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 22px;
        }

        .report-title-icon {
            width: 45px;
            height: 45px;
            border-radius: 13px;
            background: #eef2ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .report-title h2 {
            margin: 0;
            font-size: 20px;
        }

        .report-title p {
            margin: 5px 0 0;
            color: #94a3b8;
            font-size: 12px;
        }

        /* TABLE */

        .report-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        .report-table th {
            background: #f8fafc;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            padding: 15px 14px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .report-table td {
            padding: 17px 14px;
            font-size: 13px;
            color: #334155;
            border-bottom: 1px solid #eef2f7;
        }

        .report-table tr:hover td {
            background: #fafbff;
        }

        .branch-name {
            font-weight: 700;
            color: #172033;
        }

        .sales-amount {
            color: #15803d;
            font-weight: 700;
        }

        .purchase-amount {
            color: #1d4ed8;
            font-weight: 700;
        }

        .expense-amount {
            color: #b91c1c;
            font-weight: 700;
        }

        .profit-amount {
            color: #4f46e5;
            font-weight: 800;
        }

        /* EMPTY */

        .empty-message {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
            font-size: 13px;
        }

        /* INFO CARDS */

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-top: 22px;
        }

        .info-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 8px 22px rgba(15,23,42,0.05);
        }

        .info-card h3 {
            margin: 0 0 7px;
            font-size: 13px;
            color: #64748b;
        }

        .info-card p {
            margin: 0;
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
        }

        /* FOOTER */

        .page-footer {
            margin-top: 22px;
        }

        .dashboard-btn {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 10px;
            background: white;
            color: #4f46e5;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            box-shadow: 0 6px 18px rgba(15,23,42,0.06);
        }

        .dashboard-btn:hover {
            background: #eef2ff;
        }

        /* RESPONSIVE */

        @media (max-width: 850px) {

            .sidebar {
                width: 210px;
            }

            .main-content {
                margin-left: 210px;
                width: calc(100% - 210px);
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>


<!-- SIDEBAR -->

<div class="sidebar">

    <div class="brand">

        <div class="brand-icon">
            🤖
        </div>

        <h2>AI Business<br>Intelligence</h2>

        <p>Smart Business Management</p>

    </div>


    <div class="nav">

        <a href="index.php">
            🏠 Dashboard
        </a>

        <a href="sales.html">
            💰 Sales / Billing
        </a>

        <a href="purchases.html">
            🛒 Purchases
        </a>

        <a href="expenses.html">
            💵 Expenses
        </a>

        <a href="customers.html">
            👥 Customers
        </a>

        <a href="products.html">
            📦 Products
        </a>

        <a href="transactions.php">
            📋 Transactions
        </a>

        <a href="reports.php" class="active">
            📄 Reports
        </a>

        <a href="ai_assistant.html">
            🤖 AI Assistant
        </a>

        <a href="ai_insights.php">
            💡 AI Insights
        </a>

        <a href="analytics.php">
            📊 Analytics
        </a>

        <a href="logout.php">
            🚪 Logout
        </a>

    </div>

</div>


<!-- MAIN -->

<div class="main-content">


    <!-- HEADER -->

    <div class="page-header">

        <div>

            <h1>Business Reports</h1>

            <p>
                Branch-wise business performance and financial summary.
            </p>

        </div>

        <div class="header-icon">
            📄
        </div>

    </div>


    <!-- REPORT TABLE -->

    <div class="report-card">

        <div class="report-title">

            <div class="report-title-icon">
                📊
            </div>

            <div>

                <h2>Branch Performance Report</h2>

                <p>
                    Sales, purchases, expenses and calculated profit
                </p>

            </div>

        </div>


        <table class="report-table">

            <thead>

                <tr>

                    <th>Branch</th>
                    <th>Sales</th>
                    <th>Purchases</th>
                    <th>Expenses</th>
                    <th>Profit</th>

                </tr>

            </thead>


            <tbody>

            <?php

            if ($result->num_rows > 0) {

                while ($row = $result->fetch_assoc()) {

                    $profit =
                        $row['sales']
                        - $row['purchases']
                        - $row['expenses'];

            ?>

                <tr>

                    <td class="branch-name">
                        <?php echo $row['branch_name']; ?>
                    </td>

                    <td class="sales-amount">
                        ₹<?php echo number_format($row['sales'], 2); ?>
                    </td>

                    <td class="purchase-amount">
                        ₹<?php echo number_format($row['purchases'], 2); ?>
                    </td>

                    <td class="expense-amount">
                        ₹<?php echo number_format($row['expenses'], 2); ?>
                    </td>

                    <td class="profit-amount">
                        ₹<?php echo number_format($profit, 2); ?>
                    </td>

                </tr>

            <?php

                }

            } else {

            ?>

                <tr>

                    <td colspan="5" class="empty-message">
                        No report data found.
                    </td>

                </tr>

            <?php

            }

            ?>

            </tbody>

        </table>

    </div>


    <!-- INFORMATION CARDS -->

    <div class="info-grid">

        <div class="info-card">

            <h3>💰 Sales</h3>

            <p>
                Total revenue generated through business sales.
            </p>

        </div>


        <div class="info-card">

            <h3>🛒 Purchases</h3>

            <p>
                Total amount spent on purchasing products or resources.
            </p>

        </div>


        <div class="info-card">

            <h3>📈 Profit</h3>

            <p>
                Profit is calculated as Sales − Purchases − Expenses.
            </p>

        </div>

    </div>


    <!-- FOOTER -->

    <div class="page-footer">

        <a href="index.php" class="dashboard-btn">
            ← Back to Dashboard
        </a>

    </div>


</div>

</body>

</html>