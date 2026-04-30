<style>
.rzp-backdrop {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.6); z-index: 9999;
    display: none; align-items: center; justify-content: center;
}
.rzp-modal {
    background: #fff; width: 380px; max-width: 90%; height: 620px;
    border-radius: 8px; overflow: hidden; display: flex; flex-direction: column;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}
.rzp-header {
    background: #0d59df; color: white; padding: 15px;
    display: flex; align-items: center;
}
.rzp-logo-box {
    width: 40px; height: 40px; background: #e56b40; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-weight: bold; font-size: 20px; margin-right: 12px;
}
.rzp-title {
    flex-grow: 1;
}
.rzp-title-name { font-weight: 600; font-size: 16px; margin-bottom: 2px; }
.rzp-trusted { font-size: 11px; opacity: 0.9; display: flex; align-items: center; gap: 4px;}
.rzp-body {
    flex-grow: 1; overflow-y: auto; padding: 15px; background: #f9f9f9;
}
.rzp-section-title {
    font-size: 13px; color: #555; font-weight: 600; margin: 15px 0 10px; text-transform: uppercase;
}
.rzp-method {
    background: #fff; border: 1px solid #eee; border-radius: 6px;
    padding: 12px 15px; margin-bottom: 10px; display: flex; align-items: center;
    cursor: pointer; transition: 0.2s;
}
.rzp-method:hover { border-color: #0d59df; }
.rzp-method-icon {
    width: 30px; height: 30px; margin-right: 15px;
    background: #f4f4f4; border-radius: 4px; display: flex; align-items: center; justify-content: center;
    font-size: 18px;
}
.rzp-method-info { flex-grow: 1; }
.rzp-method-title { font-size: 14px; color: #111; font-weight: 500;}
.rzp-method-sub { font-size: 12px; color: #777; margin-top: 2px; }
.rzp-arrow { color: #ccc; font-weight: bold; }
.rzp-footer {
    padding: 15px; background: #fff; border-top: 1px solid #eee;
    display: flex; justify-content: space-between; align-items: center;
}
.rzp-amount { font-size: 22px; font-weight: bold; color: #111; }
.rzp-btn {
    background: #3399cc; color: white; border: none; padding: 12px 20px;
    border-radius: 4px; font-weight: 600; font-size: 16px; cursor: pointer;
    width: 150px; transition: 0.2s;
}
.rzp-btn:hover { background: #287a2; opacity: 0.9; }
/* Grid for UPI apps */
.rzp-upi-grid {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;
    background: #fff; border: 1px solid #eee; border-radius: 6px; padding: 15px;
}
.rzp-upi-item {
    display: flex; flex-direction: column; align-items: center; cursor: pointer;
    padding: 10px 0; border-radius: 6px; transition: 0.2s;
}
.rzp-upi-item:hover { background: #f4f8ff; }
.rzp-upi-icon {
    width: 40px; height: 40px; border-radius: 8px; border: 1px solid #eee; margin-bottom: 8px;
    display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px;
    background: #fff;
}
.rzp-upi-name { font-size: 11px; color: #555; }

.rzp-loader {
    display: none; align-items: center; justify-content: center;
    position: absolute; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(255,255,255,0.95); z-index: 10; flex-direction: column;
}
.rzp-spinner {
    width: 50px; height: 50px; border: 4px solid #f3f3f3;
    border-top: 4px solid #3399cc; border-radius: 50%;
    animation: rzp-spin 1s linear infinite; margin-bottom: 20px;
}
@keyframes rzp-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

<div class="rzp-backdrop" id="fakeRazorpayBackdrop">
    <div class="rzp-modal" style="position: relative;">
        <div class="rzp-header">
            <div style="margin-right: 15px; cursor: pointer; font-size: 20px;" onclick="closeFakeRazorpay()">←</div>
            <div class="rzp-logo-box">H</div>
            <div class="rzp-title">
                <div class="rzp-title-name">Hostel Management</div>
                <div class="rzp-trusted">✓ Razorpay Trusted Business</div>
            </div>
        </div>
        
        <div class="rzp-body">
            <div class="rzp-section-title">Preferred methods</div>
            <div class="rzp-method" onclick="simulateFakePayment()">
                <div class="rzp-method-icon" style="color: #d32f2f;">🏦</div>
                <div class="rzp-method-info">
                    <div class="rzp-method-title">ICICI Bank - Netbanking</div>
                </div>
                <div class="rzp-arrow">></div>
            </div>
            
            <div class="rzp-section-title">UPI, Cards & Other Methods</div>
            
            <div class="rzp-upi-grid">
                <div class="rzp-upi-item" onclick="simulateFakePayment()">
                    <div class="rzp-upi-icon" style="color: #4285F4;">G</div>
                    <div class="rzp-upi-name">Google Pay</div>
                </div>
                <div class="rzp-upi-item" onclick="simulateFakePayment()">
                    <div class="rzp-upi-icon" style="color: #5f259f;">P</div>
                    <div class="rzp-upi-name">PhonePe</div>
                </div>
                <div class="rzp-upi-item" onclick="simulateFakePayment()">
                    <div class="rzp-upi-icon" style="color: #00b9f5;">Pt</div>
                    <div class="rzp-upi-name">PayTM</div>
                </div>
                <div class="rzp-upi-item" onclick="simulateFakePayment()">
                    <div class="rzp-upi-icon" style="color: #666;">»</div>
                    <div class="rzp-upi-name">Other</div>
                </div>
            </div>
            
            <div style="margin-top: 15px;"></div>
            
            <div class="rzp-method" onclick="simulateFakePayment()">
                <div class="rzp-method-icon" style="color: #1976d2;">💳</div>
                <div class="rzp-method-info">
                    <div class="rzp-method-title">Pay using card</div>
                    <div class="rzp-method-sub">All Card Supported</div>
                </div>
                <div class="rzp-arrow">></div>
            </div>
            
            <div class="rzp-method" onclick="simulateFakePayment()">
                <div class="rzp-method-icon" style="color: #2e7d32;">💵</div>
                <div class="rzp-method-info">
                    <div class="rzp-method-title">Cash On delivery</div>
                    <div class="rzp-method-sub">Pay at the time of delivery</div>
                </div>
                <div class="rzp-arrow">></div>
            </div>

            <div class="rzp-method" onclick="simulateFakePayment()">
                <div class="rzp-method-icon" style="color: #0d47a1;">🏦</div>
                <div class="rzp-method-info">
                    <div class="rzp-method-title">Net banking</div>
                    <div class="rzp-method-sub">All Indian banks</div>
                </div>
                <div class="rzp-arrow">></div>
            </div>
        </div>
        
        <div class="rzp-footer">
            <div class="rzp-amount">₹ <span id="fakeRzpAmount">0</span></div>
            <button class="rzp-btn" onclick="simulateFakePayment()">Continue</button>
        </div>
        
        <div class="rzp-loader" id="fakeRzpLoader">
            <div class="rzp-spinner"></div>
            <div style="font-weight: bold; color: #333; font-size: 16px;">Processing Payment...</div>
            <div style="color: #777; font-size: 13px; margin-top: 8px;">Please do not close this window</div>
        </div>
    </div>
</div>

<script>
let currentSuccessCallback = null;

function openFakeRazorpay(amount, onSuccess) {
    document.getElementById('fakeRzpAmount').innerText = amount.toLocaleString('en-IN');
    document.getElementById('fakeRazorpayBackdrop').style.display = 'flex';
    document.getElementById('fakeRzpLoader').style.display = 'none';
    currentSuccessCallback = onSuccess;
}

function closeFakeRazorpay() {
    document.getElementById('fakeRazorpayBackdrop').style.display = 'none';
}

function simulateFakePayment() {
    document.getElementById('fakeRzpLoader').style.display = 'flex';
    setTimeout(() => {
        closeFakeRazorpay();
        if (currentSuccessCallback) {
            currentSuccessCallback();
        }
    }, 2000); // Simulate 2 second loading
}
</script>
