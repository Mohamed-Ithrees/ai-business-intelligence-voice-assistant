<?php

include "db.php";

$sql = "SELECT 
            t.transaction_id,
            b.branch_name,
            c.customer_name,
            p.product_name,
            t.transaction_type,
            t.transaction_date,
            t.quantity,
            t.amount,
            t.payment_status,
            t.input_method
        FROM transactions t
        LEFT JOIN branches b ON t.branch_id = b.branch_id
        LEFT JOIN customers c ON t.customer_id = c.customer_id
        LEFT JOIN products p ON t.product_id = p.product_id
        ORDER BY t.transaction_id DESC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Transactions | AI Business Intelligence</title>

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

        /* TRANSACTION CARD */

        .transaction-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 28px rgba(15,23,42,0.07);
            overflow-x: auto;
        }

        .transaction-title {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px;
        }

        .transaction-title-icon {
            width: 45px;
            height: 45px;
            border-radius: 13px;
            background: #eef2ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .transaction-title h2 {
            margin: 0;
            font-size: 20px;
        }

        .transaction-title p {
            margin: 5px 0 0;
            color: #94a3b8;
            font-size: 12px;
        }

        /* TABLE */

        .transaction-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1050px;
        }

        .transaction-table th {
            background: #f8fafc;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .transaction-table td {
            padding: 14px 12px;
            font-size: 12px;
            color: #334155;
            border-bottom: 1px solid #eef2f7;
            white-space: nowrap;
        }

        .transaction-table tr:hover td {
            background: #fafbff;
        }

        .transaction-id {
            font-weight: 700;
            color: #4f46e5;
        }

        /* TYPE BADGES */

        .type-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
        }

        .sale-badge {
            background: #dcfce7;
            color: #15803d;
        }

        .purchase-badge {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .expense-badge {
            background: #fee2e2;
            color: #b91c1c;
        }

        /* PAYMENT */

        .payment-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
        }

        .paid {
            background: #dcfce7;
            color: #15803d;
        }

        .pending {
            background: #fef3c7;
            color: #b45309;
        }

        .partial {
            background: #e0e7ff;
            color: #4338ca;
        }

        /* INPUT METHOD */

        .input-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #f1f5f9;
            color: #475569;
            font-size: 10px;
            font-weight: 700;
        }

        /* EMPTY */

        .empty-message {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
            font-size: 13px;
        }

        /* FOOTER */

        .page-footer {
            margin-top: 20px;
            display: flex;
            justify-content: flex-start;
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

        @media (max-width: 850px) {

            .sidebar {
                width: 210px;
            }

            .main-content {
                margin-left: 210px;
                width: calc(100% - 210px);
            }

            .page-header {
                padding: 25px;
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

        <a href="transactions.php" class="active">
            📋 Transactions
        </a>

        <a href="reports.php">
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


<!-- MAIN CONTENT -->

<div class="main-content">


    <!-- HEADER -->

    <div class="page-header">

        <div>

            <h1>Transaction Management</h1>

            <p>
                View and monitor all business transactions in one place.
            </p>

        </div>

        <div class="header-icon">
            📋
        </div>

    </div>


    <!-- TRANSACTION TABLE -->

    <div class="transaction-card">

        <div class="transaction-title">

            <div class="transaction-title-icon">
                📊
            </div>

            <div>

                <h2>All Transactions</h2>

                <p>
                    Sales, purchases, expenses and AI-generated transactions
                </p>

            </div>

        </div>


        <table class="transaction-table">

            <thead>

                <tr>

                    <th>ID</th>
                    <th>Branch</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Quantity</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Input</th>

                </tr>

            </thead>


            <tbody>

            <?php

            if ($result->num_rows > 0) {

                while ($row = $result->fetch_assoc()) {

                    $type_class = "";

                    if ($row['transaction_type'] == "Sale") {
                        $type_class = "sale-badge";
                    } elseif ($row['transaction_type'] == "Purchase") {
                        $type_class = "purchase-badge";
                    } else {
                        $type_class = "expense-badge";
                    }


                    $payment_class = "";

                    if ($row['payment_status'] == "Paid") {
                        $payment_class = "paid";
                    } elseif ($row['payment_status'] == "Pending") {
                        $payment_class = "pending";
                    } else {
                        $payment_class = "partial";
                    }

            ?>

                <tr>

                    <td class="transaction-id">
                        <?php echo $row['transaction_id']; ?>
                    </td>

                    <td>
                        <?php echo $row['branch_name']; ?>
                    </td>

                    <td>
                        <?php echo $row['customer_name'] ?? '-'; ?>
                    </td>

                    <td>
                        <?php echo $row['product_name'] ?? '-'; ?>
                    </td>

                    <td>

                        <span class="type-badge <?php echo $type_class; ?>">

                            <?php echo $row['transaction_type']; ?>

                        </span>

                    </td>

                    <td>
                        <?php echo $row['transaction_date']; ?>
                    </td>

                    <td>
                        <?php echo $row['quantity']; ?>
                    </td>

                    <td>
                        ₹<?php echo number_format($row['amount'], 2); ?>
                    </td>

                    <td>

                        <span class="payment-badge <?php echo $payment_class; ?>">

                            <?php echo $row['payment_status']; ?>

                        </span>

                    </td>

                    <td>

                        <span class="input-badge">

                            <?php echo $row['input_method']; ?>

                        </span>

                    </td>

                </tr>

            <?php

                }

            } else {

            ?>

                <tr>

                    <td colspan="10" class="empty-message">

                        No transactions found.

                    </td>

                </tr>

            <?php

            }

            ?>

            </tbody>

        </table>

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