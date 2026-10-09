<?php

include "db.php";

$user_input = $_POST['user_input'];

$data = json_encode([
    "text" => $user_input
]);

$ch = curl_init("http://127.0.0.1:5000/process");

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if ($response === false) {
    die("AI Backend connection failed: " . curl_error($ch));
}

curl_close($ch);

$result = json_decode($response, true);


// Extract AI data
$transaction_type = $result['transaction_type'];
$branch = $result['branch'];
$customer = $result['customer'];
$product = $result['product'];
$quantity = $result['quantity'];
$amount = $result['amount'];


// Find or create branch
$stmt = $conn->prepare(
    "SELECT branch_id FROM branches WHERE branch_name = ?"
);

$stmt->bind_param("s", $branch);
$stmt->execute();

$branch_result = $stmt->get_result();

if ($branch_result->num_rows > 0) {

    $row = $branch_result->fetch_assoc();
    $branch_id = $row['branch_id'];

} else {

    $stmt = $conn->prepare(
        "INSERT INTO branches (branch_name) VALUES (?)"
    );

    $stmt->bind_param("s", $branch);
    $stmt->execute();

    $branch_id = $conn->insert_id;
}


// Find or create customer
$customer_id = null;

if (!empty($customer)) {

    $stmt = $conn->prepare(
        "SELECT customer_id FROM customers WHERE customer_name = ?"
    );

    $stmt->bind_param("s", $customer);
    $stmt->execute();

    $customer_result = $stmt->get_result();

    if ($customer_result->num_rows > 0) {

        $row = $customer_result->fetch_assoc();
        $customer_id = $row['customer_id'];

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO customers (customer_name) VALUES (?)"
        );

        $stmt->bind_param("s", $customer);
        $stmt->execute();

        $customer_id = $conn->insert_id;
    }
}


// Find or create product
$product_id = null;

if (!empty($product)) {

    $stmt = $conn->prepare(
        "SELECT product_id FROM products WHERE product_name = ?"
    );

    $stmt->bind_param("s", $product);
    $stmt->execute();

    $product_result = $stmt->get_result();

    if ($product_result->num_rows > 0) {

        $row = $product_result->fetch_assoc();
        $product_id = $row['product_id'];

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO products (product_name) VALUES (?)"
        );

        $stmt->bind_param("s", $product);
        $stmt->execute();

        $product_id = $conn->insert_id;
    }
}


// Save transaction
$payment_status = "Paid";
$input_method = $_POST['input_method'] ?? "Text";
$description = "AI Assistant Transaction";
$date = date("Y-m-d");

$stmt = $conn->prepare(
    "INSERT INTO transactions
    (branch_id, customer_id, product_id, transaction_type,
     transaction_date, quantity, amount, payment_status,
     input_method, description)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "iiissidsss",
    $branch_id,
    $customer_id,
    $product_id,
    $transaction_type,
    $date,
    $quantity,
    $amount,
    $payment_status,
    $input_method,
    $description
);

$transaction_saved = $stmt->execute();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AI Transaction Result</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            color: #172033;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .page-wrapper {
            width: 100%;
            max-width: 900px;
            margin: auto;
        }

        /* Header */

        .page-header {
            background: linear-gradient(135deg, #111827, #4f46e5);
            color: white;
            border-radius: 22px;
            padding: 30px 35px;
            margin-bottom: 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 12px 30px rgba(79, 70, 229, 0.20);
        }

        .header-text h1 {
            font-size: 30px;
            margin-bottom: 8px;
        }

        .header-text p {
            color: #dbeafe;
            font-size: 14px;
        }

        .ai-icon {
            width: 62px;
            height: 62px;
            border-radius: 18px;
            background: rgba(255,255,255,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
        }

        /* Original Input */

        .input-card {
            background: white;
            border-radius: 20px;
            padding: 25px 30px;
            margin-bottom: 22px;
            border: 1px solid #e8edf5;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.06);
        }

        .section-title {
            font-size: 17px;
            font-weight: bold;
            margin-bottom: 14px;
            color: #111827;
        }

        .original-input {
            background: #f8fafc;
            border-left: 4px solid #4f46e5;
            border-radius: 10px;
            padding: 16px;
            color: #475569;
            font-size: 15px;
            line-height: 1.6;
        }

        /* Extracted Information */

        .result-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 22px;
            border: 1px solid #e8edf5;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.06);
        }

        .result-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 22px;
        }

        .result-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
        }

        .result-header h2 {
            font-size: 20px;
            color: #111827;
        }

        .result-header p {
            font-size: 13px;
            color: #64748b;
            margin-top: 3px;
        }

        .data-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .data-box {
            background: #f8fafc;
            border-radius: 14px;
            padding: 17px;
            border: 1px solid #edf1f6;
        }

        .data-label {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 7px;
        }

        .data-value {
            font-size: 16px;
            font-weight: bold;
            color: #172033;
        }

        .amount-box {
            grid-column: 1 / -1;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
        }

        .amount-box .data-value {
            font-size: 27px;
            color: #2563eb;
        }

        /* Success */

        .success-card {
            background: white;
            border-radius: 20px;
            padding: 25px 30px;
            margin-bottom: 22px;
            border: 1px solid #bbf7d0;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.06);
        }

        .success-content {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .success-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #dcfce7;
            color: #16a34a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 27px;
            font-weight: bold;
            flex-shrink: 0;
        }

        .success-card h3 {
            color: #15803d;
            font-size: 17px;
            margin-bottom: 5px;
        }

        .success-card p {
            color: #64748b;
            font-size: 14px;
        }

        /* Error */

        .error-card {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 20px;
            padding: 25px 30px;
            margin-bottom: 22px;
        }

        .error-card h3 {
            color: #c2410c;
            margin-bottom: 7px;
        }

        .error-card p {
            color: #7c2d12;
            font-size: 14px;
        }

        /* Buttons */

        .actions {
            background: white;
            border-radius: 20px;
            padding: 25px 30px;
            display: flex;
            justify-content: center;
            gap: 14px;
            border: 1px solid #e8edf5;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.06);
        }

        .btn {
            text-decoration: none;
            padding: 14px 23px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: bold;
            transition: 0.2s;
        }

        .btn-primary {
            background: #4f46e5;
            color: white;
        }

        .btn-primary:hover {
            background: #4338ca;
        }

        .btn-secondary {
            background: #eef2f7;
            color: #172033;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .footer-text {
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
            margin-top: 20px;
        }

        @media (max-width: 650px) {

            body {
                padding: 20px 12px;
            }

            .page-header {
                padding: 25px;
            }

            .header-text h1 {
                font-size: 24px;
            }

            .data-grid {
                grid-template-columns: 1fr;
            }

            .amount-box {
                grid-column: auto;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                text-align: center;
            }

        }

    </style>

</head>

<body>

<div class="page-wrapper">

    <!-- Header -->

    <div class="page-header">

        <div class="header-text">

            <h1>AI Transaction Result</h1>

            <p>
                AI has analyzed and processed your business transaction.
            </p>

        </div>

        <div class="ai-icon">
            🤖
        </div>

    </div>


    <!-- Original Input -->

    <div class="input-card">

        <div class="section-title">
            Original Input
        </div>

        <div class="original-input">
            <?php echo htmlspecialchars($user_input); ?>
        </div>

    </div>


    <!-- AI Extracted Information -->

    <div class="result-card">

        <div class="result-header">

            <div class="result-icon">
                ✨
            </div>

            <div>

                <h2>AI Extracted Information</h2>

                <p>
                    Important transaction details identified by AI
                </p>

            </div>

        </div>


        <div class="data-grid">

            <div class="data-box">

                <div class="data-label">
                    Transaction Type
                </div>

                <div class="data-value">
                    <?php echo htmlspecialchars($transaction_type); ?>
                </div>

            </div>


            <div class="data-box">

                <div class="data-label">
                    Branch
                </div>

                <div class="data-value">
                    <?php echo htmlspecialchars($branch); ?>
                </div>

            </div>


            <div class="data-box">

                <div class="data-label">
                    Customer
                </div>

                <div class="data-value">
                    <?php echo htmlspecialchars($customer ?? '-'); ?>
                </div>

            </div>


            <div class="data-box">

                <div class="data-label">
                    Product
                </div>

                <div class="data-value">
                    <?php echo htmlspecialchars($product ?? '-'); ?>
                </div>

            </div>


            <div class="data-box">

                <div class="data-label">
                    Quantity
                </div>

                <div class="data-value">
                    <?php echo htmlspecialchars($quantity ?? '-'); ?>
                </div>

            </div>


            <div class="data-box">

    <div class="data-label">
        Input Method
    </div>

    <div class="data-value">
        🎤 <?php echo htmlspecialchars($input_method); ?>
    </div>

</div>


            <div class="data-box amount-box">

                <div class="data-label">
                    Transaction Amount
                </div>

                <div class="data-value">
                    ₹<?php echo number_format((float)($amount ?? 0), 2); ?>
                </div>

            </div>

        </div>

    </div>


    <!-- Database Status -->

    <?php if ($transaction_saved): ?>

        <div class="success-card">

            <div class="success-content">

                <div class="success-icon">
                    ✓
                </div>

                <div>

                    <h3>Transaction Saved to Database!</h3>

                    <p>
                        Your AI transaction has been successfully added to the business database.
                    </p>

                </div>

            </div>

        </div>

    <?php else: ?>

        <div class="error-card">

            <h3>❌ Transaction Could Not Be Saved</h3>

            <p>
                Please check the database connection and try again.
            </p>

        </div>

    <?php endif; ?>


    <!-- Actions -->

    <div class="actions">

        <a href="ai_assistant.html" class="btn btn-primary">
            ✨ Try Another Transaction
        </a>

        <a href="index.php" class="btn btn-secondary">
            Go to Dashboard
        </a>

    </div>


    <div class="footer-text">
        AI Business Intelligence • Intelligent Transaction Processing
    </div>

</div>

</body>

</html>

<?php

$conn->close();

?>