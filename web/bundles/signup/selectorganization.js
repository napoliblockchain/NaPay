let isMerchant = document.getElementById("is_merchant");
let divMerchant = document.querySelector('#termini_pos');

isMerchant.addEventListener('change', function (ev) {
    ev.stopPropagation();
    ev.preventDefault();

    let valueField = isMerchant.value;
    console.log('Scelta socio', valueField);
    if (valueField == 1) {
        divMerchant.style.display = '';
    } else {
        divMerchant.style.display = 'none';
    }
});