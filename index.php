<?php
require_once 'db.php';

// Get next quotation number
function getNextQuotationNumber() {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(numero_cotizacion, 5) AS UNSIGNED)) as max_num FROM cotizaciones WHERE numero_cotizacion LIKE 'COT-%'");
    $result = $stmt->fetch();
    $nextNum = ($result['max_num'] ?? 0) + 1;
    return 'COT-' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);
}

$nextQuotationNumber = getNextQuotationNumber();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Cotizaciones</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        
        .search-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .product-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s ease;
            margin-bottom: 1rem;
        }
        
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }
        
        .quotation-panel {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
        }
        
        .btn-add-product {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 500;
        }
        
        .btn-add-product:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            color: white;
        }
        
        .quotation-item {
            border-bottom: 1px solid #eee;
            padding: 1rem 0;
        }
        
        .quotation-item:last-child {
            border-bottom: none;
        }
        
        .total-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 1rem;
        }
        
        .header-title {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Left Panel - Product Search -->
            <div class="col-lg-8">
                <div class="search-container">
                    <h2 class="header-title mb-4">Búsqueda de Productos</h2>
                    
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <input type="text" id="searchInput" class="form-control form-control-lg" 
                                   placeholder="Buscar productos... (ej: dimax, detergente, jabón)">
                        </div>
                        <div class="col-md-4">
                            <button type="button" id="searchBtn" class="btn btn-primary btn-lg w-100">
                                Buscar
                            </button>
                        </div>
                    </div>
                    
                    <div id="searchResults" class="row">
                        <div class="col-12 text-center text-muted">
                            <p>Ingrese un término de búsqueda para ver los productos disponibles</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Panel - Quotation -->
            <div class="col-lg-4">
                <div class="quotation-panel p-4">
                    <h3 class="header-title mb-4">Cotización</h3>
                    
                    <!-- Quotation Info -->
                    <div class="mb-4">
                        <div class="row mb-2">
                            <div class="col-6">
                                <small class="text-muted">Número:</small>
                                <div class="fw-bold" id="quotationNumber"><?php echo $nextQuotationNumber; ?></div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Fecha:</small>
                                <div class="fw-bold" id="quotationDate"><?php echo date('d/m/Y H:i'); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Client Information -->
                    <div class="mb-4">
                        <h5 class="mb-3">Información del Cliente</h5>
                        <div class="mb-2">
                            <input type="text" id="clientCI" class="form-control" placeholder="CI/NIT" onblur="searchClient()">
                        </div>
                        <div class="mb-2">
                            <input type="text" id="clientName" class="form-control" placeholder="Nombre">
                        </div>
                        <div class="mb-2">
                            <input type="text" id="clientLastName" class="form-control" placeholder="Apellido">
                        </div>
                        <div class="mb-2">
                            <input type="text" id="clientPhone" class="form-control" placeholder="Celular">
                        </div>
                    </div>
                    
                    <!-- Validity -->
                    <div class="mb-4">
                        <label class="form-label">Vigencia de la oferta:</label>
                        <select id="quotationValidity" class="form-select">
                            <option value="1">1 día</option>
                            <option value="2">2 días</option>
                            <option value="3" selected>3 días</option>
                            <option value="4">4 días</option>
                            <option value="5">5 días</option>
                            <option value="6">6 días</option>
                            <option value="7">7 días</option>
                            <option value="30">1 mes</option>
                        </select>
                    </div>
                    
                    <!-- Selected Products -->
                    <div class="mb-4">
                        <h5 class="mb-3">Productos Seleccionados</h5>
                        <div id="selectedProducts">
                            <div class="text-center text-muted py-4">
                                <p>No hay productos seleccionados</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Observations -->
                    <div class="mb-4">
                        <label class="form-label">Observaciones:</label>
                        <textarea id="observations" class="form-control" rows="3" placeholder="Detalles adicionales..."></textarea>
                    </div>
                    
                    <!-- Total Section -->
                    <div class="total-section">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold">Total:</span>
                            <span class="fw-bold fs-4" id="totalAmount">Bs. 0.00</span>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12 mb-2">
                                <button type="button" id="saveQuotationBtn" class="btn btn-success w-100" disabled>
                                    Guardar Cotización
                                </button>
                            </div>
                            <div class="col-12">
                                <button type="button" id="exportPDFBtn" class="btn btn-outline-primary w-100" disabled>
                                    Exportar PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        let selectedProducts = [];
        let productCounter = 0;
        
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                searchProducts();
            }
        });
        
        document.getElementById('searchBtn').addEventListener('click', searchProducts);
        
        function searchProducts() {
            const searchTerm = document.getElementById('searchInput').value.trim();
            
            if (searchTerm.length < 2) {
                document.getElementById('searchResults').innerHTML = 
                    '<div class="col-12 text-center text-muted"><p>Ingrese al menos 2 caracteres para buscar</p></div>';
                return;
            }
            
            // Show loading
            document.getElementById('searchResults').innerHTML = 
                '<div class="col-12 text-center"><div class="spinner-border" role="status"></div></div>';
            
            // AJAX call to search products
            fetch('search_products.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ search: searchTerm })
            })
            .then(response => response.json())
            .then(data => {
                displayProducts(data);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('searchResults').innerHTML = 
                    '<div class="col-12 text-center text-danger"><p>Error al buscar productos</p></div>';
            });
        }
        
        function displayProducts(products) {
            const resultsContainer = document.getElementById('searchResults');
            
            if (products.length === 0) {
                resultsContainer.innerHTML = 
                    '<div class="col-12 text-center text-muted"><p>No se encontraron productos</p></div>';
                return;
            }
            
            let html = '';
            products.forEach(product => {
                html += `
                    <div class="col-12 mb-3">
                        <div class="product-card p-3">
                            <div class="row align-items-center">
                                <div class="col-2">
                                    <img src="images/${product.imagen}" alt="${product.nombre}" 
                                         class="product-image" onerror="this.src='images/default-product.jpg'">
                                </div>
                                <div class="col-6">
                                    <h6 class="mb-1">${product.nombre}</h6>
                                    <p class="text-muted mb-0 small">${product.descripcion}</p>
                                    <span class="badge bg-primary">Bs. ${parseFloat(product.precio_unitario).toFixed(2)}</span>
                                </div>
                                <div class="col-2">
                                    <input type="number" class="form-control form-control-sm" 
                                           id="qty_${product.id}" value="1" min="1" max="999">
                                    <small class="text-muted">Cantidad</small>
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-add-product btn-sm w-100" 
                                            onclick="addProduct(${product.id}, '${product.nombre}', '${product.descripcion}', '${product.imagen}', ${product.precio_unitario})">
                                        Agregar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            resultsContainer.innerHTML = html;
        }
        
        function addProduct(id, name, description, image, price) {
            const quantity = parseInt(document.getElementById(`qty_${id}`).value) || 1;
            const subtotal = quantity * price;
            
            // Check if product already exists
            const existingIndex = selectedProducts.findIndex(p => p.id === id);
            
            if (existingIndex >= 0) {
                // Update existing product
                selectedProducts[existingIndex].quantity += quantity;
                selectedProducts[existingIndex].subtotal = selectedProducts[existingIndex].quantity * price;
            } else {
                // Add new product
                selectedProducts.push({
                    id: id,
                    name: name,
                    description: description,
                    image: image,
                    price: price,
                    quantity: quantity,
                    subtotal: subtotal
                });
            }
            
            updateQuotationDisplay();
            
            // Reset quantity input
            document.getElementById(`qty_${id}`).value = 1;
            
            // Show success message
            showToast('Producto agregado a la cotización', 'success');
        }
        
        function removeProduct(index) {
            selectedProducts.splice(index, 1);
            updateQuotationDisplay();
            showToast('Producto eliminado de la cotización', 'info');
        }
        
        function updateProductQuantity(index, newQuantity) {
            if (newQuantity <= 0) {
                removeProduct(index);
                return;
            }
            
            selectedProducts[index].quantity = newQuantity;
            selectedProducts[index].subtotal = newQuantity * selectedProducts[index].price;
            updateQuotationDisplay();
        }
        
        function updateQuotationDisplay() {
            const container = document.getElementById('selectedProducts');
            const totalElement = document.getElementById('totalAmount');
            const saveBtn = document.getElementById('saveQuotationBtn');
            const exportBtn = document.getElementById('exportPDFBtn');
            
            if (selectedProducts.length === 0) {
                container.innerHTML = '<div class="text-center text-muted py-4"><p>No hay productos seleccionados</p></div>';
                totalElement.textContent = 'Bs. 0.00';
                saveBtn.disabled = true;
                exportBtn.disabled = true;
                return;
            }
            
            let html = '';
            let total = 0;
            
            selectedProducts.forEach((product, index) => {
                total += product.subtotal;
                html += `
                    <div class="quotation-item">
                        <div class="row align-items-center">
                            <div class="col-3">
                                <img src="images/${product.image}" alt="${product.name}" 
                                     class="img-fluid rounded" style="max-height: 40px;" 
                                     onerror="this.src='images/default-product.jpg'">
                            </div>
                            <div class="col-6">
                                <h6 class="mb-0 small">${product.name}</h6>
                                <small class="text-muted">Bs. ${product.price.toFixed(2)} c/u</small>
                            </div>
                            <div class="col-3">
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control" value="${product.quantity}" 
                                           min="1" max="999" onchange="updateProductQuantity(${index}, this.value)">
                                    <button class="btn btn-outline-danger btn-sm" type="button" 
                                            onclick="removeProduct(${index})" title="Eliminar">×</button>
                                </div>
                                <small class="text-muted">Bs. ${product.subtotal.toFixed(2)}</small>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
            totalElement.textContent = `Bs. ${total.toFixed(2)}`;
            
            // Enable buttons if there are products and client info
            const hasClientInfo = document.getElementById('clientCI').value.trim() !== '' && 
                                 document.getElementById('clientName').value.trim() !== '';
            
            saveBtn.disabled = !hasClientInfo;
            exportBtn.disabled = !hasClientInfo;
        }
        
        // Client search functionality
        function searchClient() {
            const ci = document.getElementById('clientCI').value.trim();
            
            if (ci.length < 3) return;
            
            fetch('search_client.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ci: ci })
            })
            .then(response => response.json())
            .then(data => {
                if (data.found) {
                    document.getElementById('clientName').value = data.client.nombre;
                    document.getElementById('clientLastName').value = data.client.apellido;
                    document.getElementById('clientPhone').value = data.client.celular || '';
                    showToast('Cliente encontrado', 'success');
                }
                updateQuotationDisplay();
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        // Save quotation
        document.getElementById('saveQuotationBtn').addEventListener('click', function() {
            if (selectedProducts.length === 0) {
                showToast('Debe agregar al menos un producto', 'warning');
                return;
            }
            
            const quotationData = {
                numero_cotizacion: document.getElementById('quotationNumber').textContent,
                cliente: {
                    ci: document.getElementById('clientCI').value.trim(),
                    nombre: document.getElementById('clientName').value.trim(),
                    apellido: document.getElementById('clientLastName').value.trim(),
                    celular: document.getElementById('clientPhone').value.trim()
                },
                validez: document.getElementById('quotationValidity').value,
                observaciones: document.getElementById('observations').value.trim(),
                productos: selectedProducts,
                total: selectedProducts.reduce((sum, p) => sum + p.subtotal, 0)
            };
            
            // Validate required fields
            if (!quotationData.cliente.ci || !quotationData.cliente.nombre) {
                showToast('Complete la información del cliente', 'warning');
                return;
            }
            
            // Show loading
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
            
            fetch('save_quotation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(quotationData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Cotización guardada exitosamente', 'success');
                    // Reset form or redirect
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showToast('Error al guardar: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error al guardar la cotización', 'danger');
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = 'Guardar Cotización';
            });
        });
        
        // Export PDF
        document.getElementById('exportPDFBtn').addEventListener('click', function() {
            // Show modal for PDF options
            showPDFOptionsModal();
        });
        
        function showPDFOptionsModal() {
            // Create modal HTML
            const modalHTML = `
                <div class="modal fade" id="pdfOptionsModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Opciones de Exportación PDF</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Moneda:</label>
                                    <select id="pdfCurrency" class="form-select">
                                        <option value="BOB">Bolivianos (Bs.)</option>
                                        <option value="USD">Dólares (USD)</option>
                                    </select>
                                </div>
                                <div class="mb-3" id="exchangeRateDiv" style="display: none;">
                                    <label class="form-label">Tipo de Cambio (Bs. por USD):</label>
                                    <input type="number" id="exchangeRate" class="form-control" value="6.96" step="0.01">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Formato:</label>
                                    <select id="pdfFormat" class="form-select">
                                        <option value="carta">Carta</option>
                                        <option value="rollo">Rollo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" onclick="generatePDF()">Generar PDF</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            const existingModal = document.getElementById('pdfOptionsModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            // Show exchange rate field when USD is selected
            document.getElementById('pdfCurrency').addEventListener('change', function() {
                const exchangeDiv = document.getElementById('exchangeRateDiv');
                exchangeDiv.style.display = this.value === 'USD' ? 'block' : 'none';
            });
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('pdfOptionsModal'));
            modal.show();
        }
        
        function generatePDF() {
            const currency = document.getElementById('pdfCurrency').value;
            const format = document.getElementById('pdfFormat').value;
            const exchangeRate = document.getElementById('exchangeRate').value;
            
            const quotationData = {
                numero_cotizacion: document.getElementById('quotationNumber').textContent,
                cliente: {
                    ci: document.getElementById('clientCI').value.trim(),
                    nombre: document.getElementById('clientName').value.trim(),
                    apellido: document.getElementById('clientLastName').value.trim(),
                    celular: document.getElementById('clientPhone').value.trim()
                },
                validez: document.getElementById('quotationValidity').value,
                observaciones: document.getElementById('observations').value.trim(),
                productos: selectedProducts,
                total: selectedProducts.reduce((sum, p) => sum + p.subtotal, 0),
                currency: currency,
                format: format,
                exchange_rate: exchangeRate
            };
            
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'generate_pdf.php';
            form.target = '_blank';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'quotation_data';
            input.value = JSON.stringify(quotationData);
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('pdfOptionsModal'));
            modal.hide();
        }
        
        // Toast notification function
        function showToast(message, type = 'info') {
            const toastHTML = `
                <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            let toastContainer = document.getElementById('toastContainer');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toastContainer';
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                toastContainer.style.zIndex = '9999';
                document.body.appendChild(toastContainer);
            }
            
            toastContainer.insertAdjacentHTML('beforeend', toastHTML);
            
            const toastElement = toastContainer.lastElementChild;
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
            
            // Remove toast element after it's hidden
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        }
        
        // Enable buttons when client info is complete
        ['clientCI', 'clientName'].forEach(id => {
            document.getElementById(id).addEventListener('input', updateQuotationDisplay);
        });
    </script>
</body>
</html>
