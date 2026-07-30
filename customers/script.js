let customers = JSON.parse(localStorage.getItem('customers')) || [
    { name: "Vijay Chakali", email: "vijay@example.com", phone: "9876543210", address: "123 Hyderabad", status: "Active" },
    { name: "Pranith Kurra", email: "pranith@example.com", phone: "9876543211", address: "124 Hyderabad", status: "Active" },
    { name: "Sai Enugu", email: "sai@example.com", phone: "9876543212", address: "125 Hyderabad", status: "Active" },
    { name: "Raju Perala", email: "raju@example.com", phone: "9876543213", address: "126 Hyderabad", status: "Inactive" }
];

function saveToStorage() {
    localStorage.setItem('customers', JSON.stringify(customers));
}

function renderCustomers() {
    const tbody = document.getElementById('customerTableBody');
    const search = document.getElementById('search').value.toLowerCase();
    tbody.innerHTML = '';
    customers.filter(c => c.name.toLowerCase().includes(search)).forEach((cust, i) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${i + 1}</td>
            <td>${cust.name}</td>
            <td>${cust.email}</td>
            <td>${cust.phone}</td>
            <td>${cust.address}</td>
            <td class="${cust.status === 'Active' ? 'status-active' : 'status-inactive'}">${cust.status}</td>
            <td>
                <button onclick="editCustomer(${i})">Edit</button>
                <button onclick="deleteCustomer(${i})">Delete</button>
                <button onclick="viewInvoice(${i})">View Invoice</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function openAddModal() {
    document.getElementById('modalTitle').innerText = 'Add Customer';
    document.getElementById('customerForm').reset();
    document.getElementById('editIndex').value = '';
    document.getElementById('customerModal').style.display = 'flex';
}

function closeCustomerModal() {
    document.getElementById('customerModal').style.display = 'none';
}

function closeInvoiceModal() {
    document.getElementById('invoiceModal').style.display = 'none';
}

function editCustomer(index) {
    const cust = customers[index];
    document.getElementById('modalTitle').innerText = 'Edit Customer';
    document.getElementById('editIndex').value = index;
    document.getElementById('name').value = cust.name;
    document.getElementById('email').value = cust.email;
    document.getElementById('phone').value = cust.phone;
    document.getElementById('address').value = cust.address;
    document.getElementById('status').value = cust.status;
    document.getElementById('customerModal').style.display = 'flex';
}

function deleteCustomer(index) {
    if (confirm('Are you sure?')) {
        customers.splice(index, 1);
        saveToStorage();
        renderCustomers();
    }
}

function viewInvoice(index) {
    const cust = customers[index];
    const invoices = JSON.parse(localStorage.getItem('invoices')) || [];
    const customerInvoices = invoices.filter(inv => inv.customer === cust.name);
    let invoiceHTML = customerInvoices.length > 0 ? customerInvoices.map(inv => `
        <p><strong>Invoice ID:</strong> ${inv.id}</p>
        <p><strong>Name:</strong> ${cust.name}</p>
        <p><strong>Email:</strong> ${cust.email}</p>
        <p><strong>Phone:</strong> ${cust.phone}</p>
        <p><strong>Amount:</strong> ₹${inv.grandTotal}</p>
        <p><strong>Date:</strong> ${new Date(inv.date).toLocaleDateString()}</p>
        <hr>
    `).join('') : '<p>No invoices found for this customer.</p>';
    document.getElementById('invoiceDetails').innerHTML = invoiceHTML;
    document.getElementById('invoiceModal').style.display = 'flex';
}

document.getElementById('customerForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const index = document.getElementById('editIndex').value;
    const customer = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        address: document.getElementById('address').value,
        status: document.getElementById('status').value
    };
    if (index === '') {
        customers.push(customer);
    } else {
        customers[index] = customer;
    }
    saveToStorage();
    renderCustomers();
    closeCustomerModal();
});

document.getElementById('search').addEventListener('input', renderCustomers);

renderCustomers();