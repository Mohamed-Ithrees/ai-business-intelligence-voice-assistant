<?php

include "db.php";

$product_name = $_POST['product_name'];
$category = $_POST['category'];
$unit_price = $_POST['unit_price'];

$stmt = $conn->prepare(
    "INSERT INTO products
    (product_name, category, unit_price)
    VALUES (?, ?, ?)"
);

$stmt->bind_param(
    "ssd",
    $product_name,
    $category,
    $unit_price
);

if (!$stmt->execute()) {

    die("Error saving product: " . $stmt->error);

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Product Saved Successfully</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            color: #172033;
        }

        .success-card {
            width: 680px;
            max-width: 100%;
            background: #ffffff;
            border-radius: 24px;
            padding: 48px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.10);
        }

        .success-icon {
            width: 88px;
            height: 88px;
            margin: 0 auto 22px;
            border-radius: 50%;
            background: #dcfce7;
            color: #16a34a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: bold;
        }

        h1 {
            margin: 0;
            font-size: 32px;
            color: #111827;
        }

        .subtitle {
            margin: 10px 0 30px;
            color: #64748b;
            font-size: 16px;
        }

        .product-name {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .label {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .name {
            color: #7c3aed;
            font-size: 28px;
            font-weight: 700;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            text-align: left;
            margin-bottom: 28px;
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
            word-break: break-word;
        }

        .price-box {
            grid-column: 1 / -1;
            background: #f5f3ff;
            border: 1px solid #ddd6fe;
        }

        .price {
            font-size: 26px;
            color: #7c3aed;
            font-weight: 700;
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
            margin-top: 5px;
            color: #4d7c5b;
            font-size: 13px;
            font-weight: 400;
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 14px;
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

        .footer {
            margin-top: 28px;
            color: #94a3b8;
            font-size: 13px;
        }

        @media (max-width: 600px) {

            .success-card {
                padding: 32px 22px;
            }

            h1 {
                font-size: 25px;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }

            .price-box {
                grid-column: auto;
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

<div class="success-card">

    <div class="success-icon">
        ✓
    </div>

    <h1>Product Saved Successfully!</h1>

    <p class="subtitle">
        The product information has been added to your business database.
    </p>


    <div class="product-name">

        <div class="label">
            Product Name
        </div>

        <div class="name">
            <?php echo htmlspecialchars($product_name); ?>
        </div>

    </div>


    <div class="details-grid">

        <div class="detail-box">

            <div class="detail-label">
                Category
            </div>

            <div class="detail-value">
                <?php echo htmlspecialchars($category ?: '-'); ?>
            </div>

        </div>


        <div class="detail-box">

            <div class="detail-label">
                Status
            </div>

            <div class="detail-value">
                ✓ Active Product
            </div>

        </div>


        <div class="detail-box price-box">

            <div class="detail-label">
                Unit Price
            </div>

            <div class="price">
                ₹<?php echo number_format((float)$unit_price, 2); ?>
            </div>

        </div>

    </div>


    <div class="success-message">

        ✓ Product information saved successfully!

        <span>
            The product is now available for business transactions.
        </span>

    </div>


    <div class="actions">

        <a href="products.html" class="btn btn-primary">
            + Add Another Product
        </a>

        <a href="index.php" class="btn btn-secondary">
            Go to Dashboard
        </a>

    </div>


    <div class="footer">
        AI Business Intelligence • Product Management
    </div>

</div>

</body>

</html>

<?php

$conn->close();

?>