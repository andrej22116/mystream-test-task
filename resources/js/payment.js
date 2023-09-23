import Payment from './payment/payment.js';

/**
 * @typedef {string} stripClientPublicKey
 * @typedef {string} stripItemId
 */

window.addEventListener('load', async () => {
    const payment = new Payment(stripClientPublicKey, stripItemId)

    await payment.initialize();
});
