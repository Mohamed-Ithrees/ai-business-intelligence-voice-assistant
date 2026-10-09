<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include "db.php";

$result = $conn->query(
    "SELECT COALESCE(SUM(amount), 0) AS total_sales
     FROM transactions
     WHERE transaction_type = 'Sale'"
);

$row = $result->fetch_assoc();
$total_sales = $row['total_sales'];
$result = $conn->query(
    "SELECT COALESCE(SUM(amount), 0) AS total_purchases
     FROM transactions
     WHERE transaction_type = 'Purchase'"
);

$row = $result->fetch_assoc();
$total_purchases = $row['total_purchases'];
$result = $conn->query(
    "SELECT COALESCE(SUM(amount), 0) AS total_expenses
     FROM transactions
     WHERE transaction_type = 'Expense'"
);

$row = $result->fetch_assoc();
$total_expenses = $row['total_expenses'];
$total_profit = $total_sales - $total_purchases - $total_expenses;
$recent_transactions = $conn->query(
    "SELECT 
        t.transaction_date,
        t.transaction_type,
        t.amount,
        t.payment_status,
        c.customer_name,
        p.product_name
     FROM transactions t
     LEFT JOIN customers c ON t.customer_id = c.customer_id
     LEFT JOIN products p ON t.product_id = p.product_id
     ORDER BY t.transaction_id DESC
     LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AI Business Intelligence</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        body {
            background: #f4f7fb;
            color: #172033;
        }

        .app {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */

        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #111827, #1e293b);
            color: white;
            padding: 28px 18px;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
        }

        .brand {
            padding: 0 12px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .brand-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
            margin-bottom: 12px;
        }

        .brand h2 {
            font-size: 18px;
            line-height: 1.3;
        }

        .brand p {
            margin-top: 5px;
            color: #94a3b8;
            font-size: 11px;
        }

        .menu {
            margin-top: 25px;
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #cbd5e1;
            text-decoration: none;
            padding: 12px 14px;
            margin-bottom: 5px;
            border-radius: 10px;
            font-size: 14px;
            transition: 0.2s;
        }

        .menu a:hover {
            background: rgba(255,255,255,0.08);
            color: white;
        }

        .menu .active {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: white;
            box-shadow: 0 8px 20px rgba(79,70,229,0.25);
        }

        .logout {
            margin-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.08);
            padding-top: 18px;
        }

        /* MAIN */

        .main {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 30px 35px;
        }

        /* TOP BAR */

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .welcome h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .welcome p {
            color: #64748b;
            font-size: 14px;
        }

        .status {
            display: flex;
            align-items: center;
            gap: 8px;
            background: white;
            padding: 10px 16px;
            border-radius: 25px;
            box-shadow: 0 5px 18px rgba(15,23,42,0.06);
            font-size: 13px;
            font-weight: 600;
        }

        .status-dot {
            width: 9px;
            height: 9px;
            background: #22c55e;
            border-radius: 50%;
        }

        /* HERO */

        .hero {
            background: linear-gradient(135deg, #111827, #3730a3, #4f46e5);
            color: white;
            border-radius: 22px;
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            box-shadow: 0 15px 35px rgba(79,70,229,0.18);
        }

        .hero h2 {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .hero p {
            color: #dbeafe;
            font-size: 13px;
        }

        .hero-btn {
            background: rgba(255,255,255,0.15);
            color: white;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.2);
            font-size: 13px;
        }

        /* KPI */

        .section-title {
            font-size: 18px;
            margin-bottom: 15px;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 28px;
        }

        .kpi {
            background: white;
            padding: 22px;
            border-radius: 17px;
            box-shadow: 0 8px 25px rgba(15,23,42,0.06);
            position: relative;
            overflow: hidden;
        }

        .kpi::after {
            content: "";
            position: absolute;
            width: 70px;
            height: 70px;
            right: -20px;
            top: -20px;
            background: #eef2ff;
            border-radius: 50%;
        }

        .kpi-icon {
            font-size: 24px;
            margin-bottom: 16px;
        }

        .kpi-label {
            color: #64748b;
            font-size: 12px;
            margin-bottom: 7px;
        }

        .kpi-value {
            font-size: 22px;
            font-weight: 700;
        }

        .sales {
            border-top: 4px solid #2563eb;
        }

        .purchase {
            border-top: 4px solid #f59e0b;
        }

        .expense {
            border-top: 4px solid #ef4444;
        }

        .profit {
            border-top: 4px solid #16a34a;
        }

        .profit .kpi-value {
            color: #16a34a;
        }

        /* QUICK ACTIONS */

        .content-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 22px;
        }

        .panel {
            background: white;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 8px 25px rgba(15,23,42,0.06);
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .panel-header h3 {
            font-size: 17px;
        }

        .panel-header span {
            color: #94a3b8;
            font-size: 11px;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 13px;
        }

        .action {
            text-decoration: none;
            padding: 18px;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            transition: 0.2s;
        }

        .action:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 18px rgba(15,23,42,0.08);
            border-color: #c7d2fe;
        }

        .action-icon {
            font-size: 23px;
            margin-bottom: 10px;
        }

        .action h4 {
            color: #172033;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .action p {
            color: #94a3b8;
            font-size: 11px;
        }

        /* AI CARD */

        .ai-card {
            background: linear-gradient(135deg, #eef2ff, #f5f3ff);
            border: 1px solid #ddd6fe;
        }

        .ai-icon {
            width: 52px;
            height: 52px;
            border-radius: 15px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 27px;
            margin-bottom: 16px;
        }

        .ai-card h3 {
            font-size: 19px;
            margin-bottom: 8px;
        }

        .ai-card p {
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 18px;
        }

        .ai-btn {
            display: inline-block;
            background: #4f46e5;
            color: white;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
        }

        /* RESPONSIVE */

        @media (max-width: 1000px) {
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 700px) {
            .sidebar {
                width: 210px;
            }

            .main {
                margin-left: 210px;
                width: calc(100% - 210px);
                padding: 20px;
            }

            .hero {
                flex-direction: column;
                align-items: flex-start;
                gap: 18px;
            }
        }
     /* RECENT TRANSACTIONS */

.recent-panel {
    margin-top: 22px;
}

.recent-panel .panel-header a {
    color: #4f46e5;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
}

.transaction-table {
    width: 100%;
    overflow-x: auto;
}

.transaction-row {
    display: grid;
    grid-template-columns: 1.2fr 1.2fr 0.8fr 1fr 1fr 0.9fr;
    align-items: center;
    gap: 15px;
    padding: 15px 12px;
    border-bottom: 1px solid #eef2f7;
    font-size: 12px;
}

.transaction-row:last-child {
    border-bottom: none;
}

.transaction-head {
    background: #f8fafc;
    border-radius: 10px;
    color: #64748b;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.transaction-row:not(.transaction-head):hover {
    background: #fafbff;
}

.transaction-row .amount {
    font-weight: 700;
    color: #172033;
}

.transaction-row b {
    font-size: 11px;
}

.type-sale {
    color: #16a34a;
    background: #dcfce7;
    padding: 5px 9px;
    border-radius: 20px;
}

.type-purchase {
    color: #d97706;
    background: #fef3c7;
    padding: 5px 9px;
    border-radius: 20px;
}

.type-expense {
    color: #dc2626;
    background: #fee2e2;
    padding: 5px 9px;
    border-radius: 20px;
}

.status-paid {
    color: #15803d;
    background: #dcfce7;
    padding: 5px 9px;
    border-radius: 20px;
}

.status-pending {
    color: #b45309;
    background: #fef3c7;
    padding: 5px 9px;
    border-radius: 20px;
}

.status-partial {
    color: #7c3aed;
    background: #ede9fe;
    padding: 5px 9px;
    border-radius: 20px;
}

.empty-transactions {
    text-align: center;
    padding: 35px;
    color: #94a3b8;
    font-size: 13px;
}   
    </style>
</head>

<body>

<div class="app">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="brand">
            <div class="brand-icon">🤖</div>

            <h2>AI Business<br>Intelligence</h2>

            <p>Smart Business Management</p>
        </div>

        <div class="menu">

            <a href="#" class="active">🏠 Dashboard</a>

            <a href="sales.html">💰 Sales / Billing</a>

            <a href="purchases.html">🛒 Purchases</a>

            <a href="expenses.html">💸 Expenses</a>

            <a href="customers.html">👥 Customers</a>

            <a href="products.html">📦 Products</a>

            <a href="transactions.php">🔄 Transactions</a>

            <a href="reports.php">📑 Reports</a>

            <a href="ai_assistant.html">🤖 AI Assistant</a>

            <a href="ai_insights.php">💡 AI Insights</a>

            <a href="analytics.php">📊 Analytics</a>

            <div class="logout">
                <a href="logout.php">🚪 Logout</a>
            </div>

        </div>

    </aside>


    <!-- MAIN -->

    <main class="main">

        <!-- TOP BAR -->

        <div class="topbar">

            <div class="welcome">

                <h1>Good Day 👋</h1>

                <p>Welcome back to your business intelligence dashboard.</p>

            </div>

            <div class="status">

                <span class="status-dot"></span>

                AI Analysis Active

            </div>

        </div>


        <!-- HERO -->

        <section class="hero">

            <div>

                <h2>Business Overview</h2>

                <p>
                    Monitor your sales, expenses, purchases and profitability
                    from one intelligent platform.
                </p>

            </div>

            <a href="ai_insights.php" class="hero-btn">
                ✨ View AI Insights
            </a>

        </section>


        <!-- KPI -->

        <h3 class="section-title">Business Performance</h3>

        <div class="kpi-grid">

            <div class="kpi sales">

                <div class="kpi-icon">💰</div>

                <div class="kpi-label">
                    Total Sales
                </div>

                <div class="kpi-value">
                    ₹<?php echo number_format($total_sales, 2); ?>
                </div>

            </div>


            <div class="kpi purchase">

                <div class="kpi-icon">🛒</div>

                <div class="kpi-label">
                    Total Purchases
                </div>

                <div class="kpi-value">
                    ₹<?php echo number_format($total_purchases, 2); ?>
                </div>

            </div>


            <div class="kpi expense">

                <div class="kpi-icon">💸</div>

                <div class="kpi-label">
                    Total Expenses
                </div>

                <div class="kpi-value">
                    ₹<?php echo number_format($total_expenses, 2); ?>
                </div>

            </div>


            <div class="kpi profit">

                <div class="kpi-icon">📈</div>

                <div class="kpi-label">
                    Net Profit
                </div>

                <div class="kpi-value">
                    ₹<?php echo number_format($total_profit, 2); ?>
                </div>

            </div>

        </div>


        <!-- LOWER CONTENT -->

        <div class="content-grid">


            <!-- QUICK ACTIONS -->

            <section class="panel">

                <div class="panel-header">

                    <h3>Quick Actions</h3>

                    <span>Manage your business</span>

                </div>


                <div class="action-grid">

                    <a href="sales.html" class="action">

                        <div class="action-icon">💰</div>

                        <h4>New Sale</h4>

                        <p>Create a new sales transaction</p>

                    </a>


                    <a href="purchases.html" class="action">

                        <div class="action-icon">🛒</div>

                        <h4>Add Purchase</h4>

                        <p>Record a new purchase</p>

                    </a>


                    <a href="expenses.html" class="action">

                        <div class="action-icon">💸</div>

                        <h4>Add Expense</h4>

                        <p>Record business expenses</p>

                    </a>


                    <a href="ai_assistant.html" class="action">

                        <div class="action-icon">🎤</div>

                        <h4>AI Transaction</h4>

                        <p>Enter transaction using AI</p>

                    </a>

                </div>

            </section>


            <!-- AI ASSISTANT -->

            <section class="panel ai-card">

                <div class="ai-icon">
                    🤖
                </div>

                <h3>AI Business Assistant</h3>

                <p>
                    Enter a transaction using text, voice or document.
                    AI automatically extracts the details and stores them
                    in your business database.
                </p>

                <a href="ai_assistant.html" class="ai-btn">
                    Open AI Assistant →
                </a>

            </section>


        </div>
        <!-- RECENT TRANSACTIONS -->

        <section class="panel recent-panel">

            <div class="panel-header">
                <h3>Recent Transactions</h3>
                <a href="transactions.php">View All →</a>
            </div>

            <div class="transaction-table">

                <div class="transaction-row transaction-head">
                    <span>Customer</span>
                    <span>Product</span>
                    <span>Type</span>
                    <span>Amount</span>
                    <span>Date</span>
                    <span>Status</span>
                </div>

                <?php if ($recent_transactions && $recent_transactions->num_rows > 0): ?>

                    <?php while ($transaction = $recent_transactions->fetch_assoc()): ?>

                        <div class="transaction-row">

                            <span>
                                <?php echo htmlspecialchars($transaction['customer_name'] ?? 'Walk-in Customer'); ?>
                            </span>

                            <span>
                                <?php echo htmlspecialchars($transaction['product_name'] ?? 'N/A'); ?>
                            </span>

                            <span>
                                <b class="type-<?php echo strtolower($transaction['transaction_type']); ?>">
                                    <?php echo htmlspecialchars($transaction['transaction_type']); ?>
                                </b>
                            </span>

                            <span class="amount">
                                ₹<?php echo number_format($transaction['amount'], 2); ?>
                            </span>

                            <span>
                                <?php echo date("d M Y", strtotime($transaction['transaction_date'])); ?>
                            </span>

                            <span>
                                <b class="status-<?php echo strtolower($transaction['payment_status']); ?>">
                                    <?php echo htmlspecialchars($transaction['payment_status']); ?>
                                </b>
                            </span>

                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="empty-transactions">
                        No transactions found.
                    </div>

                <?php endif; ?>

            </div>

        </section>
    </main>

</div>

</body>
</html>