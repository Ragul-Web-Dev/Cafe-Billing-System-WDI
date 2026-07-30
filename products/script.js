console.log("Script loaded");

document.addEventListener("DOMContentLoaded", () => {
    console.log("DOM loaded");

    // Initialize products if localStorage is empty or invalid
    let products = JSON.parse(localStorage.getItem('products'));
    if (!products || !Array.isArray(products) || products.some(p => !p || typeof p !== 'object' || !p.title)) {
        console.log("Initializing default products due to invalid or empty localStorage");
        products = [
            {
                title: "Espresso",
                subtext: "Strong black coffee",
                details: "Single shot of rich espresso",
                price: 80,
                taxPercent: 5,
                image_url: "https://via.placeholder.com/100?text=Espresso"
            },
            {
                title: "Cappuccino",
                subtext: "Frothy delight",
                details: "Espresso with steamed milk and foam",
                price: 120,
                taxPercent: 5,
                image_url: "https://via.placeholder.com/100?text=Cappuccino"
            },
            {
                title: "Iced Latte",
                subtext: "Chilled coffee",
                details: "Espresso with cold milk over ice",
                price: 150,
                taxPercent: 5,
                image_url: "https://via.placeholder.com/100?text=Iced+Latte"
            }
        ];
        localStorage.setItem('products', JSON.stringify(products));
        console.log("Products saved to localStorage:", products);
    } else {
        console.log("Products loaded from localStorage:", products);
    }

    const openform = document.getElementById("openform");
    const formWrapper = document.getElementById("formWrapper");
    const closeform = document.getElementById("closeform");
    const productForm = document.getElementById("productForm");
    const totalproductscount = document.getElementById("totalProducts");
    const productCardContainer = document.getElementById("productCardContainer");
    const searchInput = document.querySelector(".input-field");

    if (!productCardContainer) {
        console.error("productCardContainer not found");
        return;
    }

    openform.addEventListener("click", () => {
        formWrapper.classList.add("open");
        productForm.reset();
    });

    closeform.addEventListener("click", () => {
        formWrapper.classList.remove("open");
    });

    function displayProducts(products) {
        console.log("Displaying products:", products);
        productCardContainer.innerHTML = "";
        const search = searchInput.value.toLowerCase();
        products
            .filter(p => p && typeof p === 'object' && p.title && typeof p.title === 'string')
            .filter(p => p.title.toLowerCase().includes(search))
            .forEach((product, index) => {
                console.log("Rendering product:", product.title);
                const card = document.createElement("div");
                card.className = "card";
                card.innerHTML = `
                    <div class="inner-card">
                        <div class="product-main">
                            <div class="img-div">
                                <img class="img" src="${product.image_url || 'https://via.placeholder.com/100'}" alt="${product.title}" onerror="this.src='https://via.placeholder.com/100';">
                            </div>
                            <div class="content-div">
                                <div class="content">
                                    <h3 class="title">${product.title}</h3>
                                    <p class="subtext">${product.subtext || ''}</p>
                                    <p class="details">${product.details || ''}</p>
                                </div>
                                <div class="card-footer">
                                    <h4 class="product-price">₹${product.price || 0}</h4>
                                    <button class="add-item-button" onclick="addToBilling('${product.title}')">Add Item</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                productCardContainer.appendChild(card);
            });
    }

    function addToBilling(title) {
        console.log(`Adding ${title} to billing`);
        const product = products.find(p => p.title === title);
        if (!product) {
            console.error(`Product ${title} not found`);
            return;
        }

        let billingItems = JSON.parse(localStorage.getItem('billingItems')) || [];
        billingItems.push({
            title: product.title,
            price: product.price,
            taxPercent: product.taxPercent,
            quantity: 1
        });
        localStorage.setItem('billingItems', JSON.stringify(billingItems));
        console.log("Billing items updated:", billingItems);
        // Optionally redirect to Billing module
        window.location.href = '../billing_module/index.html';
    }

    function updateProductCount(count) {
        console.log("Updating product count:", count);
        totalproductscount.textContent = count;
    }

    productForm.addEventListener("submit", (e) => {
        e.preventDefault();
        console.log("Form submitted");
        const title = document.getElementById("title").value.trim();
        const subtext = document.getElementById("subtext").value.trim();
        const details = document.getElementById("details").value.trim();
        const price = parseFloat(document.getElementById("price").value);

        if (!title || isNaN(price) || price <= 0) {
            alert("Please provide a valid title and price.");
            return;
        }

        const product = {
            title,
            subtext,
            details,
            price,
            taxPercent: 5,
            image_url: "https://via.placeholder.com/100?text=" + encodeURIComponent(title)
        };
        console.log("New product:", product);
        products.push(product);
        localStorage.setItem('products', JSON.stringify(products));
        displayProducts(products);
        updateProductCount(products.length);
        formWrapper.classList.remove("open");
        productForm.reset();
    });

    searchInput.addEventListener('input', () => displayProducts(products));

    displayProducts(products);
    updateProductCount(products.length);
});