// cart.js - shared cart logic and AJAX handlers
document.addEventListener('DOMContentLoaded', function(){
  initCartJS();
});

function initCartJS(){
  // Add-to-cart buttons
  document.querySelectorAll('.add-cart-btn').forEach(btn => {
    btn.removeEventListener('click', addCartHandler);
    btn.addEventListener('click', addCartHandler);
  });

  // Delete buttons
  document.querySelectorAll('.ajax-del').forEach(btn => {
    btn.removeEventListener('click', delHandler);
    btn.addEventListener('click', delHandler);
  });

  // Qty inputs
  document.querySelectorAll('.ajax-qty').forEach(input => {
    input.removeEventListener('change', qtyChangeHandler);
    input.addEventListener('change', qtyChangeHandler);
  });

  // Re-run calculations if present
  if (typeof updateCalculations === 'function') updateCalculations();
}

function addCartHandler(e){
  e.preventDefault();
  const id = this.dataset.id || (new URL(this.href, location.href).searchParams.get('id'));
  if (!id) return;
  ajaxCartAction('add', id).then(resp => {
    if (resp.html) replaceCartHtml(resp.html);
  });
}

function delHandler(e){
  e.preventDefault();
  const id = this.dataset.id;
  if (!id) return;
  if (!confirm('Remove this item from cart?')) return;
  ajaxCartAction('remove', id).then(resp => {
    if (resp.html) replaceCartHtml(resp.html);
  });
}

function qtyChangeHandler(e){
  const id = this.dataset.id;
  const q = parseInt(this.value) || 0;
  ajaxCartAction('update', id, q).then(resp => {
    if (resp.html) replaceCartHtml(resp.html);
  });
}

function replaceCartHtml(html){
  const container = document.querySelector('.cart-summary');
  if (!container) return;
  // Keep the heading if present; if server returns only inner content, ensure heading remains
  const heading = container.querySelector('h2');
  container.innerHTML = html;
  if (heading) {
    // If server response omitted heading, re-add it
    if (!container.querySelector('h2')) {
      const h = document.createElement('h2'); h.textContent = 'Order Summary';
      container.insertBefore(h, container.firstChild);
    }
  }
  // Re-initialize handlers for newly injected DOM
  initCartJS();
}

async function ajaxCartAction(action, id, qty){
  const form = new FormData();
  form.append('action', action);
  form.append('id', id);
  if (typeof qty !== 'undefined') form.append('qty', qty);

  const resp = await fetch('ajax_cart.php', { method: 'POST', body: form, credentials: 'same-origin' });
  if (!resp.ok) return {};
  try { return await resp.json(); } catch(e){ return {}; }
}

// Export updateCalculations placeholder if not defined by server-side cart
if (typeof updateCalculations !== 'function') {
  window.updateCalculations = function(){};
}

// --- Cart calculation logic (moved from inline PHP script) ---
let discountAmount = 0;
let currentInput = '';
let selectedPaymentMethod = 'CASH';
let cartTotal = 0;

function readCartTotalFromDom(){
  const meta = document.getElementById('cart-meta');
  if (meta && meta.dataset.total) cartTotal = parseFloat(meta.dataset.total) || 0;
}

function updateCalculations() {
  readCartTotalFromDom();
  const finalTotal = cartTotal - discountAmount;
  const cashInput = document.getElementById('cash-input');
  const cash = cashInput ? (parseFloat(cashInput.value) || 0) : 0;
  const change = Math.max(0, cash - finalTotal);

  const subtotalEl = document.getElementById('subtotal');
  const discountEl = document.getElementById('discount-amount');
  const finalEl = document.getElementById('final-total');
  const changeEl = document.getElementById('change-amount');

  if (subtotalEl) subtotalEl.textContent = '₱' + cartTotal.toFixed(2);
  if (discountEl) discountEl.textContent = '₱' + discountAmount.toFixed(2);
  if (finalEl) finalEl.textContent = '₱' + finalTotal.toFixed(2);
  if (changeEl) changeEl.textContent = '₱' + change.toFixed(2);
}

function appendToInput(val) {
  currentInput += val;
  const cashInput = document.getElementById('cash-input');
  if (cashInput) cashInput.value = currentInput;
  updateCalculations();
}

function clearInput() {
  currentInput = '';
  const cashInput = document.getElementById('cash-input');
  if (cashInput) cashInput.value = '';
  updateCalculations();
}

function applyDiscount() {
  const amount = prompt('Enter discount amount (₱):');
  if (amount !== null) {
    discountAmount = parseFloat(amount) || 0;
    currentInput = '';
    updateCalculations();
  }
}

function holdTransaction() {
  alert('Transaction held. You can resume it later.');
}

function voidTransaction() {
  if (confirm('Are you sure you want to void this transaction?')) {
    // submit hidden void form if exists
    const f = document.getElementById('void-cart-form');
    if (f) f.submit();
  }
}

function openDrawer() { alert('Opening cash drawer...'); }

function selectPaymentMethod(method) { selectedPaymentMethod = method; alert('Payment method selected: ' + method); }

// bind cash input
document.addEventListener('input', function(e){ if (e.target && e.target.id === 'cash-input') updateCalculations(); });

// Run once on load
setTimeout(updateCalculations, 20);
