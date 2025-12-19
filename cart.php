<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['void_cart'])) {
    unset($_SESSION['cart']);
}

include 'database.php';

// Make sure cart exists
$cart = $_SESSION['cart'] ?? [];
$ids = array_keys($cart);

// Make sure $total exists
$total = $total ?? 0;
?>

<!-- Checkout Button -->
<a class="btn checkout-btn" href="checkout.php">CHECKOUT</a>

<!-- Cart behaviour JS loaded separately in `js/cart.js` -->

<div id="cart-meta" data-total="<?= $total ?>" style="display:none;"></div>

<!-- Number Pad -->
<form id="void-cart-form" method="post" style="display:none;">
    <input type="hidden" name="void_cart" value="1">
</form>

<div class="number-pad">

    <div class="numpad-row">
        <button type="button" onclick="appendToInput('1')">1</button>
        <button type="button" onclick="appendToInput('2')">2</button>
        <button type="button" onclick="appendToInput('3')">3</button>
        <button type="button" class="func-btn" onclick="holdTransaction()">HOLD</button>
    </div>

    <div class="numpad-row">
        <button type="button" onclick="appendToInput('4')">4</button>
        <button type="button" onclick="appendToInput('5')">5</button>
        <button type="button" onclick="appendToInput('6')">6</button>
        <button type="button" class="func-btn clr-btn" onclick="clearInput()">CLR</button>
    </div>

    <div class="numpad-row">
        <button type="button" onclick="appendToInput('7')">7</button>
        <button type="button" onclick="appendToInput('8')">8</button>
        <button type="button" onclick="appendToInput('9')">9</button>
        <button type="button" class="func-btn disc-btn" onclick="applyDiscount()">DISC</button>
    </div>

    <div class="numpad-row">
        <button type="button" onclick="appendToInput('00')">00</button>
        <button type="button" onclick="appendToInput('0')">0</button>
        <button type="button" onclick="appendToInput('.')">.</button>
        <button type="button" class="func-btn void-btn" onclick="voidTransaction()">VOID</button>
    </div>

</div>

<!-- Checkout Button Again -->
<a class="btn checkout-btn" href="checkout.php">CHECKOUT</a>

<script>
let discountAmount = 0;
let currentInput = '';
let selectedPaymentMethod = 'CASH';
let cartTotal = <?= $total ?>;

function updateCalculations() {
    const finalTotal = cartTotal - discountAmount;
    const cash = parseFloat(document.getElementById('cash-input')?.value) || 0;
    const change = Math.max(0, cash - finalTotal);

    document.getElementById('subtotal')?.textContent = '₱' + cartTotal.toFixed(2);
    document.getElementById('discount-amount')?.textContent = '₱' + discountAmount.toFixed(2);
    document.getElementById('final-total')?.textContent = '₱' + finalTotal.toFixed(2);
    document.getElementById('change-amount')?.textContent = '₱' + change.toFixed(2);
}

function appendToInput(val) {
    currentInput += val;
    document.getElementById('cash-input').value = currentInput;
    updateCalculations();
}

function clearInput() {
    currentInput = '';
    document.getElementById('cash-input').value = '';
    updateCalculations();
}

function applyDiscount() {
    const amount = prompt('Enter discount amount (₱):');
    if (amount) {
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
        document.getElementById('void-cart-form').submit();
    }
}

function openDrawer() {
    alert('Opening cash drawer...');
}

function selectPaymentMethod(method) {
    selectedPaymentMethod = method;
    alert('Payment method selected: ' + method);
}

document.getElementById('cash-input')?.addEventListener('input', updateCalculations);
updateCalculations();
</script>
