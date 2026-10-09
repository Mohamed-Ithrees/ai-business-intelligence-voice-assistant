<?php

include "db.php";

/* =========================
   BRANCH-WISE FINANCIAL DATA
========================= */

$result = $conn->query("
    SELECT
        b.branch_name,

        COALESCE(SUM(
            CASE
                WHEN t.transaction_type = 'Sale'
                THEN t.amount ELSE 0
            END
        ), 0) AS sales,

        COALESCE(SUM(
            CASE
                WHEN t.transaction_type = 'Purchase'
                THEN t.amount ELSE 0
            END
        ), 0) AS purchases,

        COALESCE(SUM(
            CASE
                WHEN t.transaction_type = 'Expense'
                THEN t.amount ELSE 0
            END
        ), 0) AS expenses

    FROM branches b

    LEFT JOIN transactions t
        ON b.branch_id = t.branch_id

    GROUP BY b.branch_id, b.branch_name
    ORDER BY b.branch_name
");


/* =========================
   CHART DATA
========================= */

$chart_result = $conn->query("
    SELECT
        b.branch_name,

        COALESCE(SUM(
            CASE
                WHEN t.transaction_type = 'Sale'
                THEN t.amount ELSE 0
            END
        ), 0) AS sales,

        COALESCE(SUM(
            CASE
                WHEN t.transaction_type = 'Purchase'
                THEN t.amount ELSE 0
            END
        ), 0) AS purchases,

        COALESCE(SUM(
            CASE
                WHEN t.transaction_type = 'Expense'
                THEN t.amount ELSE 0
            END
        ), 0) AS expenses

    FROM branches b

    LEFT JOIN transactions t
        ON b.branch_id = t.branch_id

    GROUP BY b.branch_id, b.branch_name
    ORDER BY b.branch_name
");

$branchNames = [];
$salesData = [];
$purchaseData = [];
$expenseData = [];

while ($chart = $chart_result->fetch_assoc()) {

    $branchNames[] = $chart['branch_name'];
    $salesData[] = (float)$chart['sales'];
    $purchaseData[] = (float)$chart['purchases'];
    $expenseData[] = (float)$chart['expenses'];
}


/* =========================
   MONTHLY SALES
========================= */

$monthly_result = $conn->query("
    SELECT
        DATE_FORMAT(transaction_date, '%b %Y') AS month,
        SUM(amount) AS sales

    FROM transactions

    WHERE transaction_type = 'Sale'

    GROUP BY YEAR(transaction_date), MONTH(transaction_date)

    ORDER BY YEAR(transaction_date), MONTH(transaction_date)
");

$monthlyLabels = [];
$monthlySales = [];

while ($monthly = $monthly_result->fetch_assoc()) {

    $monthlyLabels[] = $monthly['month'];
    $monthlySales[] = (float)$monthly['sales'];
}


/* =========================
   PROFIT DATA
========================= */

$profit_result = $conn->query("
    SELECT
        b.branch_name,

        COALESCE(SUM(
            CASE
                WHEN t.transaction_type = 'Sale'
                THEN t.amount ELSE 0
            END
        ), 0)

        -

        COALESCE(SUM(
            CASE
                WHEN t.transaction_type = 'Purchase'
                THEN t.amount ELSE 0
            END
        ), 0)

        -

        COALESCE(SUM(
            CASE
                WHEN t.transaction_type = 'Expense'
                THEN t.amount ELSE 0
            END
        ), 0) AS profit

    FROM branches b

    LEFT JOIN transactions t
        ON b.branch_id = t.branch_id

    GROUP BY b.branch_id, b.branch_name

    ORDER BY b.branch_name
");

$profitLabels = [];
$profitData = [];

while ($profit = $profit_result->fetch_assoc()) {

    $profitLabels[] = $profit['branch_name'];
    $profitData[] = (float)$profit['profit'];
}


/* =========================
   OVERALL TOTALS
========================= */

$totalSales = 0;
$totalPurchases = 0;
$totalExpenses = 0;

$total_result = $conn->query("
    SELECT
        transaction_type,
        SUM(amount) AS total

    FROM transactions

    GROUP BY transaction_type
");

while ($total = $total_result->fetch_assoc()) {

    if ($total['transaction_type'] == 'Sale') {
        $totalSales = (float)$total['total'];
    }

    if ($total['transaction_type'] == 'Purchase') {
        $totalPurchases = (float)$total['total'];
    }

    if ($total['transaction_type'] == 'Expense') {
        $totalExpenses = (float)$total['total'];
    }
}

$totalProfit = $totalSales - $totalPurchases - $totalExpenses;

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Business Analytics</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6fb;
    color: #172033;
}

.app-layout {
    display: flex;
    min-height: 100vh;
}


/* =========================
   SIDEBAR
========================= */

.sidebar {
    width: 260px;
    min-height: 100vh;
    background: linear-gradient(180deg, #111827, #1e293b);
    position: fixed;
    left: 0;
    top: 0;
    padding: 28px 18px;
    color: white;
}

.brand {
    padding: 0 14px 25px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    margin-bottom: 20px;
}

.brand-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    background: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    margin-bottom: 12px;
}

.brand h2 {
    margin: 0;
    font-size: 20px;
}

.brand p {
    margin: 6px 0 0;
    color: #94a3b8;
    font-size: 12px;
}

.nav a {
    display: block;
    text-decoration: none;
    color: #cbd5e1;
    padding: 13px 14px;
    border-radius: 10px;
    margin-bottom: 6px;
    font-size: 14px;
    transition: 0.2s;
}

.nav a:hover {
    background: rgba(255,255,255,0.08);
    color: white;
}

.nav a.active {
    background: #4f46e5;
    color: white;
}


/* =========================
   MAIN CONTENT
========================= */

.main-content {
    margin-left: 260px;
    width: calc(100% - 260px);
    padding: 32px;
}


/* =========================
   PAGE HEADER
========================= */

.page-header {
    background: linear-gradient(135deg, #111827, #4f46e5);
    color: white;
    border-radius: 20px;
    padding: 30px 34px;
    margin-bottom: 25px;
}

.page-header h1 {
    margin: 0;
    font-size: 32px;
}

.page-header p {
    margin: 9px 0 0;
    color: #dbeafe;
    font-size: 15px;
}


/* =========================
   KPI CARDS
========================= */

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 25px;
}

.kpi-card {
    background: white;
    border-radius: 18px;
    padding: 22px;
    box-shadow: 0 8px 25px rgba(15,23,42,0.06);
    border: 1px solid #eef2f7;
}

.kpi-label {
    color: #64748b;
    font-size: 13px;
    margin-bottom: 9px;
}

.kpi-value {
    font-size: 25px;
    font-weight: 700;
}

.sales-value {
    color: #2563eb;
}

.purchase-value {
    color: #10b981;
}

.expense-value {
    color: #f59e0b;
}

.profit-value {
    color: #7c3aed;
}


/* =========================
   CHART GRID
========================= */

.chart-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 22px;
    margin-bottom: 22px;
}

.chart-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 8px 25px rgba(15,23,42,0.06);
    border: 1px solid #eef2f7;
}

.chart-card.full {
    grid-column: 1 / -1;
}

.chart-title {
    margin-bottom: 20px;
}

.chart-title h2 {
    margin: 0;
    font-size: 20px;
    color: #111827;
}

.chart-title p {
    margin: 6px 0 0;
    font-size: 13px;
    color: #64748b;
}

.chart-wrapper {
    position: relative;
    height: 330px;
}


/* =========================
   TABLE
========================= */

.table-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 8px 25px rgba(15,23,42,0.06);
    border: 1px solid #eef2f7;
}

.table-title {
    margin-bottom: 18px;
}

.table-title h2 {
    margin: 0;
    font-size: 20px;
}

.table-title p {
    margin: 6px 0 0;
    color: #64748b;
    font-size: 13px;
}

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #f8fafc;
    color: #475569;
    font-size: 13px;
    text-align: left;
    padding: 15px;
    border-bottom: 1px solid #e2e8f0;
}

td {
    padding: 15px;
    border-bottom: 1px solid #eef2f7;
    font-size: 14px;
}

tr:hover td {
    background: #fafbff;
}

.profit-positive {
    color: #16a34a;
    font-weight: 700;
}


/* =========================
   DASHBOARD BUTTON
========================= */

.bottom-action {
    margin-top: 25px;
    text-align: center;
}

.dashboard-btn {
    display: inline-block;
    background: #4f46e5;
    color: white;
    text-decoration: none;
    padding: 14px 25px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
}

.dashboard-btn:hover {
    background: #4338ca;
}


/* =========================
   RESPONSIVE
========================= */

@media (max-width: 1100px) {

    .kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .chart-grid {
        grid-template-columns: 1fr;
    }

    .chart-card.full {
        grid-column: auto;
    }

}

@media (max-width: 800px) {

    .sidebar {
        width: 220px;
    }

    .main-content {
        margin-left: 220px;
        width: calc(100% - 220px);
        padding: 20px;
    }

}

@media (max-width: 600px) {

    .sidebar {
        display: none;
    }

    .main-content {
        margin-left: 0;
        width: 100%;
        padding: 15px;
    }

    .kpi-grid {
        grid-template-columns: 1fr;
    }

    .page-header h1 {
        font-size: 25px;
    }

}

</style>

</head>


<body>


<div class="app-layout">


<!-- SIDEBAR -->

<aside class="sidebar">

    <div class="brand">

        <div class="brand-icon">
            📊
        </div>

        <h2>AI Business</h2>

        <p>Business Intelligence</p>

    </div>


    <nav class="nav">

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
            💳 Expenses
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

        <a href="reports.php">
            📑 Reports
        </a>

        <a href="ai_assistant.php">
            🤖 AI Assistant
        </a>

        <a href="ai_insights.php">
            💡 AI Insights
        </a>

        <a href="analytics.php" class="active">
            📊 Analytics
        </a>

        <a href="index.php">
            🚪 Logout
        </a>

    </nav>

</aside>


<!-- MAIN -->

<main class="main-content">


    <!-- HEADER -->

    <div class="page-header">

        <h1>
            Business Analytics 📊
        </h1>

        <p>
            Analyze sales, purchases, expenses and profit performance.
        </p>

    </div>


    <!-- KPI CARDS -->

    <div class="kpi-grid">


        <div class="kpi-card">

            <div class="kpi-label">
                Total Sales
            </div>

            <div class="kpi-value sales-value">
                ₹<?php echo number_format($totalSales, 2); ?>
            </div>

        </div>


        <div class="kpi-card">

            <div class="kpi-label">
                Total Purchases
            </div>

            <div class="kpi-value purchase-value">
                ₹<?php echo number_format($totalPurchases, 2); ?>
            </div>

        </div>


        <div class="kpi-card">

            <div class="kpi-label">
                Total Expenses
            </div>

            <div class="kpi-value expense-value">
                ₹<?php echo number_format($totalExpenses, 2); ?>
            </div>

        </div>


        <div class="kpi-card">

            <div class="kpi-label">
                Total Profit
            </div>

            <div class="kpi-value profit-value">
                ₹<?php echo number_format($totalProfit, 2); ?>
            </div>

        </div>


    </div>


    <!-- CHARTS -->

    <div class="chart-grid">


        <!-- BRANCH FINANCIAL -->

        <div class="chart-card full">

            <div class="chart-title">

                <h2>
                    Branch-wise Financial Analysis
                </h2>

                <p>
                    Compare sales, purchases and expenses across branches.
                </p>

            </div>

            <div class="chart-wrapper">

                <canvas id="branchChart"></canvas>

            </div>

        </div>


        <!-- MONTHLY SALES -->

        <div class="chart-card">

            <div class="chart-title">

                <h2>
                    Monthly Sales Trend 📈
                </h2>

                <p>
                    Track sales performance over time.
                </p>

            </div>

            <div class="chart-wrapper">

                <canvas id="monthlySalesChart"></canvas>

            </div>

        </div>


        <!-- PROFIT -->

        <div class="chart-card">

            <div class="chart-title">

                <h2>
                    Branch-wise Profit Analysis 💰
                </h2>

                <p>
                    Compare the profit generated by each branch.
                </p>

            </div>

            <div class="chart-wrapper">

                <canvas id="profitChart"></canvas>

            </div>

        </div>


    </div>


    <!-- TABLE -->

    <div class="table-card">

        <div class="table-title">

            <h2>
                Financial Summary
            </h2>

            <p>
                Detailed branch-wise financial performance.
            </p>

        </div>


        <div class="table-wrapper">

            <table>

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

                        <td>
                            <strong>
                                <?php echo htmlspecialchars($row['branch_name']); ?>
                            </strong>
                        </td>

                        <td>
                            ₹<?php echo number_format($row['sales'], 2); ?>
                        </td>

                        <td>
                            ₹<?php echo number_format($row['purchases'], 2); ?>
                        </td>

                        <td>
                            ₹<?php echo number_format($row['expenses'], 2); ?>
                        </td>

                        <td class="profit-positive">
                            ₹<?php echo number_format($profit, 2); ?>
                        </td>

                    </tr>

                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td colspan="5" style="text-align:center;">
                            No analytics data found
                        </td>

                    </tr>

                <?php

                }

                ?>

                </tbody>

            </table>

        </div>

    </div>


    <div class="bottom-action">

        <a href="index.php" class="dashboard-btn">
            ← Back to Dashboard
        </a>

    </div>


</main>

</div>


<script>

/* =========================
   BRANCH CHART
========================= */

const branchNames = <?php echo json_encode($branchNames); ?>;

const salesData = <?php echo json_encode($salesData); ?>;

const purchaseData = <?php echo json_encode($purchaseData); ?>;

const expenseData = <?php echo json_encode($expenseData); ?>;


new Chart(
    document.getElementById("branchChart"),
    {
        type: "bar",

        data: {

            labels: branchNames,

            datasets: [

                {
                    label: "Sales",
                    data: salesData,
                    backgroundColor: "#2563EB",
                    borderRadius: 7
                },

                {
                    label: "Purchases",
                    data: purchaseData,
                    backgroundColor: "#10B981",
                    borderRadius: 7
                },

                {
                    label: "Expenses",
                    data: expenseData,
                    backgroundColor: "#F59E0B",
                    borderRadius: 7
                }

            ]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {
                    position: "top"
                }

            },

            scales: {

                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return "₹" + value.toLocaleString("en-IN");
                        }
                    }
                }

            }

        }

    }
);


/* =========================
   MONTHLY SALES
========================= */

const monthlyLabels =
    <?php echo json_encode($monthlyLabels); ?>;

const monthlySales =
    <?php echo json_encode($monthlySales); ?>;


new Chart(
    document.getElementById("monthlySalesChart"),
    {
        type: "line",

        data: {

            labels: monthlyLabels,

            datasets: [

                {
                    label: "Monthly Sales",
                    data: monthlySales,

                    borderColor: "#2563EB",
                    backgroundColor: "rgba(37,99,235,0.10)",

                    borderWidth: 3,

                    pointBackgroundColor: "#2563EB",
                    pointRadius: 4,

                    tension: 0.35,

                    fill: true

                }

            ]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {
                    position: "top"
                }

            },

            scales: {

                y: {

                    beginAtZero: true,

                    ticks: {

                        callback: function(value) {

                            return "₹" +
                                value.toLocaleString("en-IN");

                        }

                    }

                }

            }

        }

    }
);


/* =========================
   PROFIT CHART
========================= */

const profitLabels =
    <?php echo json_encode($profitLabels); ?>;

const profitData =
    <?php echo json_encode($profitData); ?>;


new Chart(
    document.getElementById("profitChart"),
    {
        type: "bar",

        data: {

            labels: profitLabels,

            datasets: [

                {
                    label: "Profit",
                    data: profitData,

                    backgroundColor: "#7C3AED",

                    borderRadius: 7

                }

            ]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {
                    position: "top"
                }

            },

            scales: {

                y: {

                    beginAtZero: true,

                    ticks: {

                        callback: function(value) {

                            return "₹" +
                                value.toLocaleString("en-IN");

                        }

                    }

                }

            }

        }

    }
);

</script>


</body>

</html>

<?php

$conn->close();

?>