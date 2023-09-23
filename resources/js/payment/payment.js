import { loadStripe } from "@stripe/stripe-js";

export default class Payment {
    /**
     *
     * @param {string} stripClientPublicKey
     * @param {string} stripItemId
     */
    constructor( stripClientPublicKey, stripItemId ) {
        if ( !stripClientPublicKey || !stripClientPublicKey.length || !stripItemId ) {
            throw new Error('Payment object can\'t be created.');
        }

        this.publicKey = stripClientPublicKey;
        this.stripe = null;
        this.itemId = stripItemId;
        this.elements = null;

        this.submit = document.getElementById('submit');

        this.handleSubmit = this.handleSubmit.bind(this);
        document
            .getElementById('payment-form')
            .addEventListener('submit', this.handleSubmit);

        this.cardCompleted = false;
        this.linkCompleted = false;
        this.setSubmitState(false);
    }

    async initialize() {
        await this.makeStripe();

        const clientSecret = await this.createPaymentIntent();

        this.elements = this.stripe.elements({ clientSecret })

        this.mountUi();
    }

    /**
     * Make Stripe object
     * @returns {Promise<void>}
     */
    async makeStripe() {
        this.stripe = await loadStripe(this.publicKey)
        if ( !this.stripe ) {
            throw new Error('Stripe is not defined');
        }
    }

    /**
     * Create Stripe Payment Intent
     * @returns {Promise<string>} Client secrete
     */
    async createPaymentIntent() {
        const { client_secret } = await fetch('/api/v1/stripe/payment/intent', {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ itemId: this.itemId }),
        }).then((r) => r.json());

        return client_secret;
    }

    /**
     * Mount Stripe elements into form
     */
    mountUi() {
        const paymentElementOptions = {
            layout: 'tabs',
        };

        const paymentElement = this.elements.create('payment', paymentElementOptions);
        paymentElement.mount('#payment-element');
        paymentElement.on('change', event => {
            this.cardCompleted = event.complete;
            this.setSubmitStateByFieldState();
        })

        const linkElement = this.elements.create('linkAuthentication');
        linkElement.mount('#link-element');
        linkElement.on('change', event => {
            this.linkCompleted = event.complete;
            this.setSubmitStateByFieldState();
        })
    }

    setSubmitStateByFieldState() {
        this.setSubmitState(this.cardCompleted && this.linkCompleted)
    }

    /**
     * Enable or disable submit button
     * @param {boolean} enabled
     */
    setSubmitState( enabled ) {
        if ( enabled ) {
            this.submit.classList.remove('btn-secondary');
            this.submit.classList.add('btn-success');
            this.submit.style.pointerEvents = 'all';
        }
        else {
            this.submit.classList.add('btn-secondary');
            this.submit.classList.remove('btn-success');
            this.submit.style.pointerEvents = 'none';
        }
    }

    async handleSubmit( e ) {
        e.preventDefault();
        this.setSubmitState(false);

        const { error } = await this.stripe.confirmPayment({
            elements: this.elements,
            confirmParams: {
                return_url: `${window.location.origin}/buy/finish`,
            },
        });

        if ( error.type === 'card_error' || error.type === 'validation_error' ) {
            document.getElementById('payment-message').innerText = error.message;
        }
        else {
            document.getElementById('payment-message').innerText = 'An unexpected error occurred.';
        }
    }
}
