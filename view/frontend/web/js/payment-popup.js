define(['jquery','Magento_Ui/js/modal/modal'],function($,modal) {
    'use strict';
    return function(config, element) {

        var lpayOptions = {
            type: 'popup',
            responsive: true,
            innerScroll: false,
            modalClass: 'lp-modal-popup',
            keepScrollPosition : true,
            opened: function() {


            },
            closed: function() {

            },
            buttons: []
        };
        var gpayOptions = {
            type: 'popup',
            responsive: true,
            innerScroll: false,
            modalClass: 'gp-modal-popup',
            keepScrollPosition : true,
            opened: function() {


            },
            closed: function() {

            },
            buttons: []
        };
        function initPaymentOptionPopup () {
            $("#open-lpay-popup, .latitude-icon .text-learnmore").on('click',function(e){
                e.stopPropagation();
                $('#lpay-model-popup').show();
                var popupdata = $('<div />').append($('#lpay-model-popup'));
                modal(lpayOptions,popupdata);
                popupdata.modal("openModal");
            });
            $("#open-genoapay-popup, .genoapay-icon .text-learnmore").on('click',function(e){
                e.stopPropagation();
                $('#gpay-model-popup').show();
                var popupdata = $('<div  style="height: 100%"/>').append($('#gpay-model-popup'));
                modal(gpayOptions,popupdata);
                popupdata.modal("openModal");
            });
        }

        $(document).ready(function () {
            initPaymentOptionPopup();
        });
        return {
            initPaymentOptionPopup: initPaymentOptionPopup

        }
    }
});
