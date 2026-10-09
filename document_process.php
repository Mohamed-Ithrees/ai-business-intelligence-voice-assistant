<?php

include "db.php";

if (!isset($_FILES['document'])) {
    die("No document uploaded.");
}

$file = $_FILES['document'];

$ch = curl_init("http://127.0.0.1:5000/process_document_file");

curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'document' => new CURLFile(
        $file['tmp_name'],
        $file['type'],
        $file['name']
    )
]);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if ($response === false) {
    die("Flask connection failed: " . curl_error($ch));
}

curl_close($ch);

$result = json_decode($response, true);

if (!$result || ($result['status'] ?? '') !== 'success') {
    die("Invalid response from Flask.");
}

$branch = $result['branch'];
$customer = $result['customer'];
$product = $result['product'];
$quantity = $result['quantity'];
$amount = $result['amount'];
$transaction_type = $result['transaction_type'];

$payment_status = "Paid";
$input_method = "Document";
$description = "AI Document Upload";


// -------------------------
// BRANCH
// -------------------------

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


// -------------------------
// CUSTOMER
// -------------------------

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


// -------------------------
// PRODUCT
// -------------------------

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


// -------------------------
// SAVE TRANSACTION
// -------------------------

$stmt = $conn->prepare(
    "INSERT INTO transactions
    (
        branch_id,
        customer_id,
        product_id,
        transaction_type,
        transaction_date,
        quantity,
        amount,
        payment_status,
        input_method,
        description
    )
    VALUES (?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "iiisidsss",
    $branch_id,
    $customer_id,
    $product_id,
    $transaction_type,
    $quantity,
    $amount,
    $payment_status,
    $input_method,
    $description
);

if (!$stmt->execute()) {
    die("Transaction save failed: " . $stmt->error);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>AI Document Result</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            color: #172033;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
        }

        .result-card {
            width: 700px;
            max-width: 100%;
            background: #ffffff;
            border-radius: 24px;
            padding: 48px 50px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.10);
            text-align: center;
        }

        .success-icon {
            width: 88px;
            height: 88px;
            margin: 0 auto 22px;
            border-radius: 50%;
            background: #dcfce7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 46px;
            color: #16a34a;
        }

        .result-card h1 {
            margin: 0;
            font-size: 32px;
            color: #111827;
        }

        .subtitle {
            margin: 10px 0 30px;
            color: #64748b;
            font-size: 16px;
        }

        .amount-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 22px;
            margin-bottom: 28px;
        }

        .amount-label {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .amount {
            font-size: 34px;
            font-weight: 700;
            color: #7c3aed;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            text-align: left;
            margin-bottom: 30px;
        }

        .detail-box {
            background: #f8fafc;
            border-radius: 14px;
            padding: 17px;
        }

        .detail-label {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 6px;
        }

        .detail-value {
            font-size: 15px;
            font-weight: 700;
            color: #172033;
        }

        .success-message {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 28px;
            color: #166534;
            font-weight: 600;
        }

        .success-message span {
            display: block;
            font-size: 13px;
            font-weight: 400;
            margin-top: 5px;
            color: #4d7c5b;
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 14px;
            margin-top: 10px;
        }

        .btn {
            text-decoration: none;
            padding: 14px 22px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            transition: 0.2s;
        }

        .btn-primary {
            background: #7c3aed;
            color: white;
        }

        .btn-primary:hover {
            background: #6d28d9;
        }

        .btn-secondary {
            background: #eef2f7;
            color: #172033;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .footer-text {
            margin-top: 28px;
            color: #94a3b8;
            font-size: 13px;
        }

        @media (max-width: 600px) {

            body {
                padding: 15px;
            }

            .result-card {
                padding: 32px 22px;
            }

            .result-card h1 {
                font-size: 25px;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

        }

    </style>

</head>

<body>

<div class="result-card">

    <div class="success-icon">
        ✓
    </div>

    <h1>Document Processed Successfully!</h1>

    <p class="subtitle">
        Your business document was analyzed and the transaction was saved.
    </p>


    <div class="amount-box">

        <div class="amount-label">
            Document Transaction Amount
        </div>

        <div class="amount">
            ₹<?php echo number_format((float)$amount, 2); ?>
        </div>

    </div>


    <div class="details-grid">

        <div class="detail-box">

            <div class="detail-label">
                Transaction Type
            </div>

            <div class="detail-value">
                <?php echo htmlspecialchars($transaction_type); ?>
            </div>

        </div>


        <div class="detail-box">

            <div class="detail-label">
                Branch
            </div>

            <div class="detail-value">
                <?php echo htmlspecialchars($branch); ?>
            </div>

        </div>


        <div class="detail-box">

            <div class="detail-label">
                Customer
            </div>

            <div class="detail-value">
                <?php echo htmlspecialchars($customer ?: '-'); ?>
            </div>

        </div>


        <div class="detail-box">

            <div class="detail-label">
                Product
            </div>

            <div class="detail-value">
                <?php echo htmlspecialchars($product ?: '-'); ?>
            </div>

        </div>


        <div class="detail-box">

            <div class="detail-label">
                Quantity
            </div>

            <div class="detail-value">
                <?php echo htmlspecialchars($quantity); ?>
            </div>

        </div>


        <div class="detail-box">

            <div class="detail-label">
                Input Method
            </div>

            <div class="detail-value">
                📄 Document
            </div>

        </div>

    </div>


    <div class="success-message">

        ✓ Document processed and saved successfully!

        <span>
            The extracted transaction has been added to your business database.
        </span>

    </div>


    <div class="actions">

        <a href="document_upload.php" class="btn btn-primary">
            + Upload Another Document
        </a>

        <a href="index.php" class="btn btn-secondary">
            Go to Dashboard
        </a>

    </div>


    <div class="footer-text">
        AI Business Intelligence • Document Processing
    </div>

</div>

</body>

</html>

<?php

$conn->close();

?>