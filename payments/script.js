const UPI_ID = 'sonu.mavumkal@oksbi';

function loadInvoices() {
    const invoices = JSON.parse(localStorage.getItem('invoices')) || [];
    const select = document.getElementById('invoice');
    select.innerHTML = '<option disabled selected>Select invoice</option>';
    invoices.forEach(inv => {
        const option = document.createElement('option');
        option.value = inv.id;
        option.textContent = `Invoice #${inv.id}`;
        select.appendChild(option);
    });
}

document.getElementById('invoice').addEventListener('change', function () {
    const invoiceId = this.value;
    const invoices = JSON.parse(localStorage.getItem('invoices')) || [];
    const invoice = invoices.find(inv => inv.id === invoiceId);
    if (invoice) {
        document.getElementById('amount').value = invoice.grandTotal;
        let itemList = '<ul>' + invoice.items.map(i => `<li>${i.product} (Qty: ${i.qty}, Total: ₹${i.total})</li>`).join('') + '</ul>';
        document.getElementById('itemsList').innerHTML = `<strong>Items:</strong>${itemList}`;
    }
});

document.querySelectorAll('input[name="paymentMode"]').forEach(radio => {
    radio.addEventListener('change', function () {
        if (this.value === 'UPI') {
            const amount = document.getElementById('amount').value;
            const url = `https://api.qrserver.com/v1/create-qr-code/?data=upi://pay?pa=${UPI_ID}&am=${amount}&cu=INR&size=150x150`;
            document.getElementById('qrCodeContainer').innerHTML = `<img src="${url}" alt="UPI QR Code">`;
        } else {
            document.getElementById('qrCodeContainer').innerHTML = '';
        }
    });
});

function submitPayment() {
    const invoiceId = document.getElementById('invoice').value;
    const amount = document.getElementById('amount').value;
    const method = document.querySelector('input[name="paymentMode"]:checked')?.value;

    if (!invoiceId || !amount || !method) {
        alert('Please fill all fields');
        return;
    }

    const receipt = {
        invoiceId,
        amount,
        method,
        status: 'Paid',
        date: new Date().toISOString()
    };

    let receipts = JSON.parse(localStorage.getItem('receipts')) || [];
    receipts.push(receipt);
    localStorage.setItem('receipts', JSON.stringify(receipts));

    document.getElementById('receipt').style.display = 'block';
    document.getElementById('receipt').innerHTML = `
        <h3>Payment Receipt</h3>
        <p><strong>Invoice:</strong> #${invoiceId}</p>
        <p><strong>Amount:</strong> ₹${amount}</p>
        <p><strong>Payment Mode:</strong> ${method}</p>
        <p><strong>Status:</strong> Paid</p>
    `;
    window.print();
}

document.addEventListener('DOMContentLoaded', loadInvoices);