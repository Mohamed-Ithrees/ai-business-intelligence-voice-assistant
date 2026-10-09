from flask import Flask, request, jsonify

import spacy
import re
from pypdf import PdfReader
app = Flask(__name__)

nlp = spacy.load("en_core_web_sm")


@app.route("/")
def home():
    return "AI Business Intelligence Backend is Running!"


@app.route("/process", methods=["POST"])
def process():

    data = request.get_json()
    user_input = data.get("text", "")

    doc = nlp(user_input)

    # Amount
    amount = None

    amount_match = re.search(
        r'(?:for|amount|spent|₹|rs\.?)\s*₹?\s*(\d+(?:,\d+)*(?:\.\d+)?)',
        user_input,
        re.IGNORECASE
    )

    if amount_match:
        amount = amount_match.group(1).replace(",", "")

        # Quantity
    quantity = None

    quantity_match = re.search(
        r'\b(1|2|3|4|5|6|7|8|9|10|one|two|three|four|five|six|seven|eight|nine|ten)\s+(laptops?|phones?|mobiles?|items?|products?)\b',
        user_input,
        re.IGNORECASE
    )

    if quantity_match:
        quantity_text = quantity_match.group(1).lower()

        number_words = {
            "one": 1,
            "two": 2,
            "three": 3,
            "four": 4,
            "five": 5,
            "six": 6,
            "seven": 7,
            "eight": 8,
            "nine": 9,
            "ten": 10
        }

        quantity = number_words.get(quantity_text, quantity_text)

    # Transaction Type
    transaction_type = "Unknown"

    if re.search(r'\bsold|sale|sales\b', user_input, re.IGNORECASE):
        transaction_type = "Sale"

    elif re.search(r'\bbought|purchase|purchased\b', user_input, re.IGNORECASE):
        transaction_type = "Purchase"

    elif re.search(
        r'\bexpense|spent|rent|salary|electricity\b',
        user_input,
        re.IGNORECASE
    ):
        transaction_type = "Expense"

            # Branch
    branch = None

    branch_match = re.search(
        r'\b(Tenkasi|Tengasi|Thengasi|Thangasi|Surandai|Madurai)\b',
        user_input,
        re.IGNORECASE
    )

    if branch_match:
        branch = branch_match.group(1)

        if branch.lower() in ["tengasi", "thengasi", "thangasi"]:
            branch = "Tenkasi"

    # Customer
    customer = None

    customer_match = re.search(
        r'\bto\s+([A-Za-z]+)',
        user_input,
        re.IGNORECASE
    )

    if customer_match:
        customer = customer_match.group(1)

    # Product
    product = None

    product_match = re.search(
        r'\b(laptops?|phones?|mobiles?|items?|products?)\b',
        user_input,
        re.IGNORECASE
    )

    if product_match:
        product = product_match.group(1)

    return jsonify({
        "status": "success",
        "input": user_input,
        "transaction_type": transaction_type,
        "branch": branch,
        "customer": customer,
        "product": product,
        "quantity": quantity,
        "amount": amount
    })


@app.route("/process_document", methods=["POST"])
def process_document():

    data = request.get_json()
    document_text = data.get("text", "")

    # Transaction Type
    transaction_type = "Sale"

    if re.search(
        r'\bbought|purchase|purchased\b',
        document_text,
        re.IGNORECASE
    ):
        transaction_type = "Purchase"

    # Branch
    branch = None

    branch_match = re.search(
        r'Branch:\s*([A-Za-z]+)',
        document_text,
        re.IGNORECASE
    )

    if branch_match:
        branch = branch_match.group(1)

    # Customer
    customer = None

    customer_match = re.search(
        r'Customer:\s*([A-Za-z]+)',
        document_text,
        re.IGNORECASE
    )

    if customer_match:
        customer = customer_match.group(1)

        # Product
    product = None

    product_match = re.search(
        r'Product\s*\n+\s*Quantity\s*\n+\s*Amount\s*\n+\s*([A-Za-z]+)',
        document_text,
        re.IGNORECASE
    )

    if product_match:
        product = product_match.group(1)

    # Quantity
    quantity = None

    quantity_match = re.search(
        r'Product\s*\n+\s*Quantity\s*\n+\s*Amount\s*\n+\s*[A-Za-z]+\s*\n+\s*(\d+)',
        document_text,
        re.IGNORECASE
    )

    if quantity_match:
        quantity = quantity_match.group(1)

    # Amount
    amount = None

    amount_match = re.search(
        r'Total Amount:\s*(?:Rs\.?|₹)?\s*(\d+(?:,\d+)*(?:\.\d+)?)',
        document_text,
        re.IGNORECASE
    )

    if amount_match:
        amount = amount_match.group(1).replace(",", "")

    return jsonify({
        "status": "success",
        "transaction_type": transaction_type,
        "branch": branch,
        "customer": customer,
        "product": product,
        "quantity": quantity,
        "amount": amount
    })
@app.route("/process_document_file", methods=["POST"])
def process_document_file():

    if "document" not in request.files:
        return jsonify({"error": "No document uploaded"}), 400

    file = request.files["document"]

    reader = PdfReader(file)

    document_text = ""

    for page in reader.pages:
        document_text += page.extract_text() or ""

    # Transaction Type
    transaction_type = "Sale"

    if re.search(
        r'\bbought|purchase|purchased\b',
        document_text,
        re.IGNORECASE
    ):
        transaction_type = "Purchase"

    # Branch
    branch = None

    branch_match = re.search(
        r'Branch:\s*([A-Za-z]+)',
        document_text,
        re.IGNORECASE
    )

    if branch_match:
        branch = branch_match.group(1)

    # Customer
    customer = None

    customer_match = re.search(
        r'Customer:\s*([A-Za-z]+)',
        document_text,
        re.IGNORECASE
    )

    if customer_match:
        customer = customer_match.group(1)

    # Product
    product = None

    product_match = re.search(
        r'Product\s*\n+\s*Quantity\s*\n+\s*Amount\s*\n+\s*([A-Za-z]+)',
        document_text,
        re.IGNORECASE
    )

    if product_match:
        product = product_match.group(1)

    # Quantity
    quantity = None

    quantity_match = re.search(
        r'Product\s*\n+\s*Quantity\s*\n+\s*Amount\s*\n+\s*[A-Za-z]+\s*\n+\s*(\d+)',
        document_text,
        re.IGNORECASE
    )

    if quantity_match:
        quantity = quantity_match.group(1)

    # Amount
    amount = None

    amount_match = re.search(
        r'Total Amount:\s*(?:Rs\.?|₹)?\s*(\d+(?:,\d+)*(?:\.\d+)?)',
        document_text,
        re.IGNORECASE
    )

    if amount_match:
        amount = amount_match.group(1).replace(",", "")

    return jsonify({
        "status": "success",
        "transaction_type": transaction_type,
        "branch": branch,
        "customer": customer,
        "product": product,
        "quantity": quantity,
        "amount": amount
    })

if __name__ == "__main__":
    app.run(debug=True)