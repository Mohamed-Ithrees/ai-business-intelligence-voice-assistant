<?php

include "db.php";

$branch = $_POST['branch'];
$supplier = $_POST['supplier'];
$product = $_POST['product'];
$quantity = $_POST['quantity'];
$amount = $_POST['amount'];
$payment_status = $_POST['payment_status'];
$date = $_POST['date'];

if ($payment_status == "Partially Paid") {
    $payment_status = "Partial";
}

/* Branch check */
$stmt = $conn->prepare(
    "SELECT branch_id FROM branches WHERE branch_name = ?"
);
$stmt->bind_param("s", $branch);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $row = $result->fetch_assoc();
    $branch_id = $row['branch_id'];

} else {

    $stmt = $conn->prepare(
        "INSERT INTO branches (branch_name) VALUES (?)"
    );
    $stmt->bind_param("s", $branch);
    $stmt->execute();

    $branch_id = $conn->insert_id;
}


/* Product check */
$stmt = $conn->prepare(
    "SELECT product_id FROM products WHERE product_name = ?"
);
$stmt->bind_param("s", $product);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $row = $result->fetch_assoc();
    $product_id = $row['product_id'];

} else {

    $stmt = $conn->prepare(
        "INSERT INTO products (product_name) VALUES (?)"
    );
    $stmt->bind_param("s", $product);
    $stmt->execute();

    $product_id = $conn->insert_id;
}


/* Save Purchase */
$stmt = $conn->prepare(
    "INSERT INTO transactions
    (branch_id, product_id, transaction_type,
     transaction_date, quantity, amount, payment_status,
     input_method, description)
    VALUES (?, ?, 'Purchase', ?, ?, ?, ?, 'Manual', ?)"
);

$description = "Purchase from " . $supplier;

$stmt->bind_param(
    "iisidss",
    $branch_id,
    $product_id,
    $date,
    $quantity,
    $amount,
    $payment_status,
    $description
);


/* =========================
   SUCCESS PAGE
   ========================= */

if ($stmt->execute()) {

    $formatted_amount = number_format((float)$amount, 2);

    echo '
    <!DOCTYPE html>
    <html lang="en">

    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Purchase Saved Successfully</title>

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
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 30px;
            }

            .success-wrapper {
                width: 100%;
                max-width: 650px;
            }

            .success-card {
                background: #ffffff;
                border-radius: 24px;
                padding: 45px;
                text-align: center;
                box-shadow: 0 15px 40px rgba(15, 23, 42, 0.12);
                border: 1px solid #e8edf5;
            }

            .success-icon {
                width: 82px;
                height: 82px;
                margin: 0 auto 25px;
                border-radius: 50%;
                background: #dcfce7;
                color: #16a34a;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 42px;
                font-weight: bold;
            }

            .success-card h1 {
                font-size: 30px;
                margin-bottom: 10px;
                color: #111827;
            }

            .success-message {
                color: #64748b;
                font-size: 15px;
                margin-bottom: 30px;
            }

            .amount-box {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                padding: 22px;
                margin-bottom: 30px;
            }

            .amount-label {
                color: #64748b;
                font-size: 14px;
                margin-bottom: 8px;
            }

            .amount {
                font-size: 30px;
                font-weight: bold;
                color: #10b981;
            }

            .details {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 14px;
                margin-bottom: 30px;
                text-align: left;
            }

            .detail-box {
                background: #f8fafc;
                border-radius: 12px;
                padding: 15px;
            }

            .detail-label {
                font-size: 12px;
                color: #64748b;
                margin-bottom: 5px;
            }

            .detail-value {
                font-size: 14px;
                font-weight: bold;
                color: #172033;
            }

            .buttons {
                display: flex;
                gap: 14px;
                justify-content: center;
            }

            .btn {
                text-decoration: none;
                padding: 14px 22px;
                border-radius: 12px;
                font-size: 14px;
                font-weight: bold;
                transition: 0.2s;
            }

            .btn-primary {
                background: #10b981;
                color: white;
            }

            .btn-primary:hover {
                background: #059669;
            }

            .btn-secondary {
                background: #eef2f7;
                color: #172033;
            }

            .btn-secondary:hover {
                background: #e2e8f0;
            }

            .footer-text {
                margin-top: 25px;
                color: #94a3b8;
                font-size: 12px;
            }

            @media (max-width: 600px) {

                .success-card {
                    padding: 30px 20px;
                }

                .success-card h1 {
                    font-size: 24px;
                }

                .details {
                    grid-template-columns: 1fr;
                }

                .buttons {
                    flex-direction: column;
                }

                .btn {
                    width: 100%;
                }

            }

        </style>

    </head>

    <body>

        <div class="success-wrapper">

            <div class="success-card">

                <div class="success-icon">
                    ✓
                </div>

                <h1>Purchase Saved Successfully!</h1>

                <p class="success-message">
                    Your purchase transaction has been recorded successfully.
                </p>

                <div class="amount-box">

                    <div class="amount-label">
                        Purchase Amount
                    </div>

                    <div class="amount">
                        ₹' . $formatted_amount . '
                    </div>

                </div>

                <div class="details">

                    <div class="detail-box">

                        <div class="detail-label">
                            Branch
                        </div>

                        <div class="detail-value">
                            ' . htmlspecialchars($branch) . '
                        </div>

                    </div>

                    <div class="detail-box">

                        <div class="detail-label">
                            Supplier
                        </div>

                        <div class="detail-value">
                            ' . htmlspecialchars($supplier) . '
                        </div>

                    </div>

                    <div class="detail-box">

                        <div class="detail-label">
                            Product
                        </div>

                        <div class="detail-value">
                            ' . htmlspecialchars($product) . '
                        </div>

                    </div>

                    <div class="detail-box">

                        <div class="detail-label">
                            Quantity
                        </div>

                        <div class="detail-value">
                            ' . htmlspecialchars($quantity) . '
                        </div>

                    </div>

                </div>

                <div class="buttons">

                    <a href="purchases.html" class="btn btn-primary">
                        + Add Another Purchase
                    </a>

                    <a href="index.php" class="btn btn-secondary">
                        Go to Dashboard
                    </a>

                </div>

                <div class="footer-text">
                    AI Business Intelligence • Purchase Management
                </div>

            </div>

        </div>

    </body>
    </html>
    ';

} else {

    echo "Error saving purchase: " . $stmt->error;
}

$conn->close();

?>