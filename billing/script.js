const products = JSON.parse(localStorage.getItem('products')) || {};
const customers = JSON.parse(localStorage.getItem('customers')) || [];

function formatINR(amount) {
    return `₹${Number(amount).toLocaleString("en-IN", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function populateCustomers() {
    const select = document.getElementById('customer');
    select.innerHTML = '<option disabled selected>Select Customer</option>' +
        customers.map(c => `<option value="${c.name}">${c.name}</option>`).join('');
}

function addProductRow() {
    const tbody = document.querySelector("#productTable tbody");
    const row = document.createElement("tr");

    const productCell = document.createElement("td");
    const select = document.createElement("select");
    select.innerHTML = `<option disabled selected>Select Product</option>` +
        products.map(p => `<option value="${p.title}">${p.title}</option>`).join('');
    select.onchange = updateRow;
    productCell.appendChild(select);

    const qtyCell = document.createElement("td");
    const qtyInput = document.createElement("input");
    qtyInput.type = "number";
    qtyInput.value = 1;
    qtyInput.min = 1;
    qtyInput.oninput = updateRow;
    qtyCell.appendChild(qtyInput);

    const priceCell = document.createElement("td");
    const taxCell = document.createElement("td");
    const totalCell = document.createElement("td");
    const removeCell = document.createElement("td");

    const removeBtn = document.createElement("button");
    removeBtn.textContent = "X";
    removeBtn.style.background = "#ff5c5c";
    removeBtn.onclick = () => {
        row.remove();
        calculateGrandTotal();
    };
    removeCell.appendChild(removeBtn);

    row.appendChild(productCell);
    row.appendChild(qtyCell);
    row.appendChild(priceCell);
    row.appendChild(taxCell);
    row.appendChild(totalCell);
    row.appendChild(removeCell);

    tbody.appendChild(row);
}

function updateRow() {
    const row = this.closest("tr");
    const select = row.children[0].querySelector("select");
    const qty = parseInt(row.children[1].querySelector("input").value);
    const product = select.value;

    if (product && qty > 0) {
        const prod = products.find(p => p.title === product);
        const price = prod.price;
        const taxPercent = prod.taxPercent || 5; // Default tax percent
        const base = price * qty;
        const tax = (base * taxPercent) / 100;
        const total = base + tax;

        row.children[2].textContent = formatINR(price);
        row.children[3].textContent = `${taxPercent}% (${formatINR(tax)})`;
        row.children[4].textContent = formatINR(total);
    } else {
        row.children[2].textContent = "";
        row.children[3].textContent = "";
        row.children[4].textContent = "";
    }

    calculateGrandTotal();
}

function calculateGrandTotal() {
    let total = 0;
    document.querySelectorAll("#productTable tbody tr").forEach(row => {
        const product = row.children[0].querySelector("select").value;
        const qty = parseInt(row.children[1].querySelector("input").value);
        if (product && qty > 0) {
            const prod = products.find(p => p.title === product);
            const price = prod.price;
            const taxPercent = prod.taxPercent || 5;
            const base = price * qty;
            const tax = (base * taxPercent) / 100;
            total += base + tax;
        }
    });
    document.getElementById("grandTotal").textContent = formatINR(total);
}

function generateInvoice() {
    const customer = document.getElementById("customer").value;
    const rows = document.querySelectorAll("#productTable tbody tr");
    const items = [];
    const invoiceId = `INV-${Math.floor(Math.random() * 1000 + 100)}`;

    rows.forEach(row => {
        const product = row.children[0].querySelector("select").value;
        const qty = parseInt(row.children[1].querySelector("input").value);
        if (product && qty > 0) {
            const prod = products.find(p => p.title === product);
            const price = prod.price;
            const taxPercent = prod.taxPercent || 5;
            const base = price * qty;
            const tax = (base * taxPercent) / 100;
            const total = base + tax;
            items.push({
                product,
                qty,
                price,
                tax: taxPercent,
                total: total.toFixed(2)
            });
        }
    });

    const grandTotal = document.getElementById("grandTotal").textContent.replace(/[^\d.]/g, '');
    const invoiceData = {
        id: invoiceId,
        customer,
        items,
        grandTotal,
        date: new Date().toISOString()
    };

    let invoices = JSON.parse(localStorage.getItem('invoices')) || [];
    invoices.push(invoiceData);
    localStorage.setItem('invoices', JSON.stringify(invoices));

    window.open(`invoice.html?invoice_id=${invoiceId}`, "_blank");
}

document.addEventListener('DOMContentLoaded', () => {
    populateCustomers();
    addProductRow();
});