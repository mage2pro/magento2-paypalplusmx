/*browser:true*/
/*global define*/
define(
        [
            'uiComponent',
            'Magento_Checkout/js/model/payment/renderer-list'
        ],
        function (
                Component,
                rendererList
                ) {
            'use strict';
            rendererList.push(
                    {
                        type: 'qbo_paypalplusmx',
                        component: 'qbo_PayPalPlusMx/js/view/payment/method-renderer/paypalplusmx-method'
                    }

            );

            /** Add view logic here if needed */
            return Component.extend({});
        }
);