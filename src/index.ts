import { createApp } from 'vue';
import App from './components/App.vue';
import './syrattach-prod.scss';
import type { SectionOptions } from './types';

class Section {
    constructor(options: SectionOptions) {
        const mountEl = options.$wrapper.find('.js-product-attachments-section')[0];
        if (!mountEl) return;

        const app = createApp(App, {
            initialFiles: options.files,
            productId: options.product_id,
        });

        app.provide('l10n', options.l10n);
        app.provide('max_upload_size', options.max_upload_size);
        app.mount(mountEl);

        options.$wrapper.trigger('section_mounted', ['attachments', this]);
    }
}

// Register on Shop's init namespace (exists in Shop 10+)
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const $shop = $ as any;
$shop.wa_shop_products = $shop.wa_shop_products || {};
$shop.wa_shop_products.init = $shop.wa_shop_products.init || {};
$shop.wa_shop_products.init.initProductAttachmentsSection = (options: SectionOptions) => new Section(options);
