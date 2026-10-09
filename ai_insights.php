<?php

include "db.php";

/* =========================
   BUSINESS SUMMARY
========================= */

$result = $conn->query("
    SELECT
        COALESCE(SUM(CASE
            WHEN transaction_type = 'Sale'
            THEN amount ELSE 0
        END), 0) AS sales,

        COALESCE(SUM(CASE
            WHEN transaction_type = 'Purchase'
            THEN amount ELSE 0
        END), 0) AS purchases,

        COALESCE(SUM(CASE
            WHEN transaction_type = 'Expense'
            THEN amount ELSE 0
        END), 0) AS expenses

    FROM transactions
");

$data = $result->fetch_assoc();

$sales = $data['sales'];
$purchases = $data['purchases'];
$expenses = $data['expenses'];

$profit = $sales - $purchases - $expenses;
/* =========================
   PURCHASE COST ANALYSIS
========================= */

$purchase_percentage = 0;

if ($sales > 0) {
    $purchase_percentage = ($purchases / $sales) * 100;
}
/* =========================
   PROFIT MARGIN
========================= */

$profit_margin = 0;

if ($sales > 0) {
    $profit_margin = ($profit / $sales) * 100;
}
/* =========================
   MONTHLY SALES ANALYSIS
========================= */

$monthly_result = $conn->query("
    SELECT
        YEAR(transaction_date) AS sale_year,
        MONTH(transaction_date) AS sale_month,
        SUM(amount) AS monthly_sales
    FROM transactions
    WHERE transaction_type = 'Sale'
    GROUP BY YEAR(transaction_date), MONTH(transaction_date)
    ORDER BY sale_year DESC, sale_month DESC
    LIMIT 2
");

$monthly_sales = [];

if ($monthly_result && $monthly_result->num_rows > 0) {
    while ($row = $monthly_result->fetch_assoc()) {
        $monthly_sales[] = $row;
    }
}
/* =========================
   MONTHLY SALES COMPARISON
========================= */

$monthly_comparison = "";

if (count($monthly_sales) >= 2) {

    $latest_sales = $monthly_sales[0]['monthly_sales'];
    $previous_sales = $monthly_sales[1]['monthly_sales'];

    if ($latest_sales > $previous_sales) {

        $monthly_comparison =
            "📈 AI Insight: Sales increased compared with the previous month.";

    } elseif ($latest_sales < $previous_sales) {

        $monthly_comparison =
            "⚠️ AI Insight: Sales decreased compared with the previous month.";

    } else {

        $monthly_comparison =
            "➡️ AI Insight: Sales remained the same compared with the previous month.";
    }

} else {

    $monthly_comparison =
        "ℹ️ AI Insight: Not enough monthly data for comparison.";
}
/* =========================
   TOP SELLING PRODUCT
========================= */

$product_result = $conn->query("
    SELECT
        p.product_name,
        COALESCE(SUM(t.amount), 0) AS total_sales
    FROM transactions t
    INNER JOIN products p
        ON t.product_id = p.product_id
    WHERE t.transaction_type = 'Sale'
    GROUP BY p.product_id, p.product_name
    ORDER BY total_sales DESC
    LIMIT 1
");

$top_product = "No sales data";
$top_product_sales = 0;

if ($product_result) {

    $product_data = $product_result->fetch_assoc();

    if ($product_data) {
        $top_product = $product_data['product_name'];
        $top_product_sales = $product_data['total_sales'];
    }
}




/* =========================
   TOP CUSTOMER
========================= */

$customer_result = $conn->query("
    SELECT
        c.customer_name,
        SUM(t.amount) AS total_sales
    FROM transactions t
    INNER JOIN customers c
        ON t.customer_id = c.customer_id
    WHERE t.transaction_type = 'Sale'
    GROUP BY c.customer_id, c.customer_name
    ORDER BY total_sales DESC
    LIMIT 1
");

$top_customer = "No customer data";
$top_customer_sales = 0;

if ($customer_result && $customer_result->num_rows > 0) {

    $customer_data = $customer_result->fetch_assoc();

    $top_customer = $customer_data['customer_name'];
    $top_customer_sales = $customer_data['total_sales'];
}
/* =========================
   TOP CUSTOMER SALES AMOUNT
========================= */

$top_customer_sales = 0;

if ($customer_result && $customer_result->num_rows > 0) {

    $customer_result->data_seek(0);
    $customer_data = $customer_result->fetch_assoc();

    $top_customer_sales = $customer_data['total_sales'];
}
/* =========================
   TOP CUSTOMER SALES AMOUNT
========================= */

$top_customer_sales = 0;

$top_customer_sales_result = $conn->query("
    SELECT COALESCE(SUM(t.amount), 0) AS total_sales
    FROM transactions t
    INNER JOIN customers c
        ON t.customer_id = c.customer_id
    WHERE t.transaction_type = 'Sale'
      AND c.customer_name = '$top_customer'
");

if ($top_customer_sales_result && $top_customer_sales_result->num_rows > 0) {
    $sales_data = $top_customer_sales_result->fetch_assoc();
    $top_customer_sales = $sales_data['total_sales'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>AI Business Insights</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #eef2f7;
    color: #172033;
}

/* MAIN CONTAINER */

.container {
    width: 94%;
    max-width: 1400px;
    margin: 30px auto;
}

/* HEADER */

.header {
    background: linear-gradient(135deg, #111827, #312e81, #4f46e5);
    color: white;
    padding: 32px;
    border-radius: 22px;
    margin-bottom: 25px;
    box-shadow: 0 12px 30px rgba(31, 41, 55, 0.20);
}

.header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.header h1 {
    font-size: 32px;
    margin-bottom: 8px;
}

.header p {
    color: #dbeafe;
    font-size: 15px;
}

.ai-status {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    padding: 10px 16px;
    border-radius: 30px;
    font-size: 14px;
}

.status-dot {
    color: #4ade80;
}

/* SECTION */

.section {
    margin-bottom: 25px;
}

.section-title {
    font-size: 21px;
    margin-bottom: 15px;
    color: #111827;
}

/* KPI CARDS */

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
}

.kpi-card {
    background: white;
    padding: 22px;
    border-radius: 18px;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
    border: 1px solid #e5e7eb;
    transition: 0.25s;
}

.kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgba(15, 23, 42, 0.14);
}

.kpi-label {
    color: #64748b;
    font-size: 14px;
    margin-bottom: 10px;
}

.kpi-value {
    font-size: 25px;
    font-weight: bold;
}

.kpi-icon {
    font-size: 25px;
    margin-bottom: 12px;
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

/* PROFIT MARGIN */

.margin-card {
    margin-top: 18px;
    background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
    border: 1px solid #bbf7d0;
    padding: 22px;
    border-radius: 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.margin-title {
    font-size: 15px;
    color: #166534;
}

.margin-value {
    font-size: 28px;
    font-weight: bold;
    color: #15803d;
}

/* HIGHLIGHTS */

.highlight-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
}

.highlight-card {
    background: white;
    padding: 24px;
    border-radius: 18px;
    box-shadow: 0 6px 18px rgba(15,23,42,0.08);
    border: 1px solid #e5e7eb;
}

.highlight-header {
    font-size: 17px;
    font-weight: bold;
    margin-bottom: 15px;
}

.highlight-name {
    font-size: 23px;
    font-weight: bold;
    color: #4f46e5;
    margin-bottom: 8px;
}

.highlight-value {
    color: #475569;
    font-size: 14px;
}

/* MONTHLY */

.monthly-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
}

.month-card {
    background: white;
    padding: 22px;
    border-radius: 18px;
    border-left: 5px solid #6366f1;
    box-shadow: 0 6px 18px rgba(15,23,42,0.08);
}

.month-name {
    font-size: 16px;
    color: #64748b;
    margin-bottom: 8px;
}

.month-value {
    font-size: 25px;
    font-weight: bold;
    color: #312e81;
}

/* AI INSIGHTS */

.ai-panel {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 8px 22px rgba(15,23,42,0.09);
    border: 1px solid #e5e7eb;
}

.ai-panel-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 21px;
    margin-bottom: 18px;
}

.insight-list p {
    background: #f8fafc;
    padding: 13px 16px;
    margin-bottom: 10px;
    border-radius: 12px;
    border-left: 4px solid #6366f1;
    color: #334155;
    font-size: 14px;
}

/* RECOMMENDATION */

.recommendation {
    margin-top: 18px;
    background: linear-gradient(135deg, #eef2ff, #f5f3ff);
    border: 1px solid #c7d2fe;
    padding: 22px;
    border-radius: 18px;
}

.recommendation h3 {
    color: #3730a3;
    margin-bottom: 14px;
}

.recommendation p {
    background: white;
    padding: 12px 15px;
    margin-bottom: 9px;
    border-radius: 10px;
    border-left: 4px solid #4f46e5;
}

/* FOOTER BUTTON */

.footer-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 25px;
}

.dashboard-btn {
    text-decoration: none;
    background: linear-gradient(135deg, #4f46e5, #312e81);
    color: white;
    padding: 13px 22px;
    border-radius: 12px;
    font-weight: bold;
    box-shadow: 0 6px 15px rgba(79,70,229,0.25);
    transition: 0.2s;
}

.dashboard-btn:hover {
    transform: translateY(-2px);
}

/* RESPONSIVE */

@media (max-width: 1000px) {

    .kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }

}

@media (max-width: 700px) {

    .kpi-grid,
    .highlight-grid,
    .monthly-grid {
        grid-template-columns: 1fr;
    }

    .header-top {
        flex-direction: column;
        align-items: flex-start;
    }

}
/* ADVANCED AI INSIGHTS */

.month-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    font-size: 22px;
}

.month-badge {
    font-size: 11px;
    background: #eef2ff;
    color: #4338ca;
    padding: 5px 10px;
    border-radius: 20px;
    font-weight: bold;
}

.month-sub {
    margin-top: 8px;
    font-size: 12px;
    color: #94a3b8;
}


/* AI TITLE */

.ai-title-icon {
    width: 45px;
    height: 45px;
    background: #eef2ff;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 23px;
}

.ai-panel-title small {
    display: block;
    margin-top: 5px;
    color: #64748b;
    font-size: 12px;
    font-weight: normal;
}


/* INSIGHT GRID */

.insight-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.insight-card {
    display: flex;
    gap: 15px;
    padding: 18px;
    border-radius: 15px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    transition: 0.25s;
}

.insight-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 18px rgba(15,23,42,0.08);
}

.insight-icon {
    min-width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.insight-content h4 {
    margin-bottom: 7px;
    font-size: 15px;
}

.insight-content p {
    color: #64748b;
    font-size: 13px;
    line-height: 1.6;
}


/* INSIGHT COLORS */

.blue .insight-icon {
    background: #dbeafe;
}

.green .insight-icon {
    background: #dcfce7;
}

.purple .insight-icon {
    background: #ede9fe;
}

.orange .insight-icon {
    background: #fef3c7;
}

.red .insight-icon {
    background: #fee2e2;
}

.teal .insight-icon {
    background: #ccfbf1;
}


/* RECOMMENDATION */

.recommendation {
    background: linear-gradient(135deg, #111827, #312e81);
    color: white;
    border: none;
    padding: 28px;
}

.recommendation-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}

.recommendation-icon {
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.15);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 25px;
}

.recommendation h3 {
    color: white;
    margin-bottom: 4px;
}

.recommendation-header p {
    color: #c7d2fe;
    font-size: 12px;
}

.recommendation-list {
    display: grid;
    gap: 10px;
}

.recommendation-item {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    background: rgba(255,255,255,0.10);
    padding: 14px;
    border-radius: 12px;
}

.recommendation-item > span {
    font-size: 20px;
}

.recommendation-item b {
    font-size: 14px;
}

.recommendation-item p {
    margin-top: 4px;
    color: #dbeafe;
    font-size: 12px;
    line-height: 1.5;
}

@media (max-width: 700px) {

    .insight-grid {
        grid-template-columns: 1fr;
    }

}
/* AI RECOMMENDATION TEXT FIX */

.recommendation-header p {
    background: transparent !important;
    color: #c7d2fe !important;
    padding: 0 !important;
    margin: 4px 0 0 0 !important;
    border: none !important;
    box-shadow: none !important;
}

.recommendation-item p {
    background: transparent !important;
    color: #dbeafe !important;
    padding: 0 !important;
    margin: 4px 0 0 0 !important;
    border: none !important;
    box-shadow: none !important;
}
</style>

</head>


<body>

<div class="container">

    <!-- HEADER -->

    <div class="header">

        <div class="header-top">

            <div>
                <h1>🤖 AI Business Insights</h1>
                <p>Intelligent analysis of your business transaction data</p>
            </div>

            <div class="ai-status">
                <span class="status-dot">●</span>
                AI Analysis Active
            </div>

        </div>

    </div>


    <!-- BUSINESS SUMMARY -->

    <div class="section">

        <div class="section-title">
            📊 Business Performance
        </div>

        <div class="kpi-grid">

            <div class="kpi-card sales">

                <div class="kpi-icon">💰</div>

                <div class="kpi-label">
                    Total Sales
                </div>

                <div class="kpi-value">
                    ₹<?php echo number_format($sales, 2); ?>
                </div>

            </div>


            <div class="kpi-card purchase">

                <div class="kpi-icon">🛒</div>

                <div class="kpi-label">
                    Total Purchases
                </div>

                <div class="kpi-value">
                    ₹<?php echo number_format($purchases, 2); ?>
                </div>

            </div>


            <div class="kpi-card expense">

                <div class="kpi-icon">💸</div>

                <div class="kpi-label">
                    Total Expenses
                </div>

                <div class="kpi-value">
                    ₹<?php echo number_format($expenses, 2); ?>
                </div>

            </div>


            <div class="kpi-card profit">

                <div class="kpi-icon">📈</div>

                <div class="kpi-label">
                    Total Profit
                </div>

                <div class="kpi-value">
                    ₹<?php echo number_format($profit, 2); ?>
                </div>

            </div>

        </div>


        <div class="margin-card">

            <div>
                <div class="margin-title">
                    📌 Current Profit Margin
                </div>

                <small>
                    AI calculated based on total sales and profit
                </small>
            </div>

            <div class="margin-value">
                <?php echo number_format($profit_margin, 2); ?>%
            </div>

        </div>

    </div>


    <!-- BUSINESS HIGHLIGHTS -->

    <div class="section">

        <div class="section-title">
            🏆 Business Highlights
        </div>

        <div class="highlight-grid">


            <div class="highlight-card">

                <div class="highlight-header">
                    📦 Top Selling Product
                </div>

                <div class="highlight-name">
                    <?php echo htmlspecialchars($top_product); ?>
                </div>

                <div class="highlight-value">
                    Total Sales:
                    <b>₹<?php echo number_format($top_product_sales, 2); ?></b>
                </div>

            </div>


            <div class="highlight-card">

                <div class="highlight-header">
                    👤 Top Customer
                </div>

                <div class="highlight-name">
                    <?php echo htmlspecialchars($top_customer); ?>
                </div>

                <div class="highlight-value">
                    Total Sales:
                    <b>₹<?php echo number_format($top_customer_sales, 2); ?></b>
                </div>

            </div>

        </div>

    </div>


    <!-- MONTHLY SALES -->

<div class="section">

    <div class="section-title">
        📅 Monthly Sales Performance
    </div>

    <div class="monthly-grid">

        <?php

        if (count($monthly_sales) > 0) {

            foreach ($monthly_sales as $month) {

                $month_name = date(
                    "F",
                    mktime(0, 0, 0, $month['sale_month'], 1)
                );

        ?>

        <div class="month-card">

            <div class="month-top">
                <span>📅</span>
                <span class="month-badge">Sales</span>
            </div>

            <div class="month-name">
                <?php echo $month_name . " " . $month['sale_year']; ?>
            </div>

            <div class="month-value">
                ₹<?php echo number_format($month['monthly_sales'], 2); ?>
            </div>

            <div class="month-sub">
                Total sales generated during this month
            </div>

        </div>

        <?php

            }

        } else {

        ?>

        <div class="month-card">

            <div class="month-name">
                Monthly Sales
            </div>

            <div class="month-value">
                No data available
            </div>

        </div>

        <?php

        }

        ?>

    </div>

</div>


<!-- AI INSIGHTS -->

<div class="section">

    <div class="ai-panel">

        <div class="ai-panel-title">

            <div class="ai-title-icon">
                🤖
            </div>

            <div>
                <div>AI Business Analysis</div>
                <small>Automated insights generated from your transaction data</small>
            </div>

        </div>


        <div class="insight-grid">

            <div class="insight-card blue">

                <div class="insight-icon">
                    📊
                </div>

                <div class="insight-content">

                    <h4>Monthly Performance</h4>

                    <p>
                        <?php echo $monthly_comparison; ?>
                    </p>

                </div>

            </div>


            <div class="insight-card green">

                <div class="insight-icon">
                    💰
                </div>

                <div class="insight-content">

                    <h4>Profitability</h4>

                    <p>

                    <?php

                    if ($profit > 0) {

                        echo "Business is currently profitable with a positive overall profit.";

                    } else {

                        echo "Business is currently running at a loss. Review costs and sales.";

                    }

                    ?>

                    </p>

                </div>

            </div>


            <div class="insight-card purple">

                <div class="insight-icon">
                    📈
                </div>

                <div class="insight-content">

                    <h4>Profit Margin</h4>

                    <p>

                    <?php

                    if ($profit_margin >= 30) {

                        echo "Profit margin is strong and indicates healthy business performance.";

                    } elseif ($profit_margin >= 10) {

                        echo "Profit margin is at a healthy level. Continue monitoring performance.";

                    } else {

                        echo "Profit margin is low. Consider improving sales and reducing costs.";

                    }

                    ?>

                    </p>

                </div>

            </div>


            <div class="insight-card orange">

                <div class="insight-icon">
                    🛒
                </div>

                <div class="insight-content">

                    <h4>Purchase Cost</h4>

                    <p>

                    <?php

                    if ($purchase_percentage >= 40) {

                        echo "Purchase cost is high compared with sales. Review supplier pricing.";

                    } elseif ($purchase_percentage >= 20) {

                        echo "Purchase cost is moderate. Monitor purchasing expenses carefully.";

                    } else {

                        echo "Purchase cost is low compared with sales.";

                    }

                    ?>

                    </p>

                </div>

            </div>


            <div class="insight-card red">

                <div class="insight-icon">
                    💸
                </div>

                <div class="insight-content">

                    <h4>Expense Control</h4>

                    <p>

                    <?php

                    if ($sales > 0) {

                        $expense_percentage =
                            ($expenses / $sales) * 100;

                        if ($expense_percentage >= 30) {

                            echo "Expense level is high. Consider reducing unnecessary expenses.";

                        } elseif ($expense_percentage >= 15) {

                            echo "Expense level is moderate. Monitor expenses regularly.";

                        } else {

                            echo "Expense level is low and well controlled.";

                        }

                    }

                    ?>

                    </p>

                </div>

            </div>


            <div class="insight-card teal">

                <div class="insight-icon">
                    🚀
                </div>

                <div class="insight-content">

                    <h4>Sales Strength</h4>

                    <p>

                    <?php

                    if ($sales > $purchases) {

                        echo "Sales are higher than purchase costs, supporting positive business performance.";

                    } else {

                        echo "Purchase costs are higher than sales. Review the cost structure.";

                    }

                    ?>

                    </p>

                </div>

            </div>

        </div>

    </div>

</div>


<!-- AI RECOMMENDATION -->

<div class="section">

    <div class="recommendation">

        <div class="recommendation-header">

            <div class="recommendation-icon">
                💡
            </div>

            <div>

                <h3>AI Recommendation</h3>

                <p>
                    Suggested actions based on current business performance
                </p>

            </div>

        </div>


        <div class="recommendation-list">

        <?php

        if ($profit > 0) {

            echo '
            <div class="recommendation-item">
                <span>✅</span>
                <div>
                    <b>Maintain profitability</b>
                    <p>Continue monitoring sales, purchases and expenses.</p>
                </div>
            </div>
            ';


            if ($top_product_sales > 0) {

                echo '
                <div class="recommendation-item">
                    <span>🏆</span>
                    <div>
                        <b>Focus on top-selling product</b>
                        <p>Maintain strong performance of <strong>'
                        . htmlspecialchars($top_product)
                        . '</strong>.</p>
                    </div>
                </div>
                ';

            }


            if ($top_customer_sales > 0) {

                echo '
                <div class="recommendation-item">
                    <span>👤</span>
                    <div>
                        <b>Maintain customer relationships</b>
                        <p>Build stronger relationships with top customer <strong>'
                        . htmlspecialchars($top_customer)
                        . '</strong>.</p>
                    </div>
                </div>
                ';

            }

        } else {

            echo '
            <div class="recommendation-item warning">
                <span>⚠️</span>
                <div>
                    <b>Review business costs</b>
                    <p>Business is currently not profitable. Review expenses and purchase costs.</p>
                </div>
            </div>
            ';

        }

        ?>

        </div>

    </div>

</div>


<!-- FOOTER -->

<div class="footer-actions">

    <a href="index.php" class="dashboard-btn">
        ← Back to Dashboard
    </a>

</div>


    <!-- FOOTER -->

    <div class="footer-actions">

        

    </div>

</div>

</body>

</html>