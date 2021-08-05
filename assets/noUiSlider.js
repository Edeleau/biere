import noUiSlider from 'nouislider';
import 'nouislider/dist/nouislider.css';

const sliderPrice = document.getElementById('priceSlider');
const sliderCapacity = document.getElementById('capacitySlider');

if (sliderPrice) {
    const min = document.getElementById('minPrice');
    const max = document.getElementById('maxPrice');

    const rangePrice = noUiSlider.create(sliderPrice, {
        start: [0,Math.ceil(max.value ,10) ],
        connect: true,
        range: {
            'min': 0,
            'max': Math.ceil(max.value ,10),
        }
    });

    rangePrice.on('slide', function (values, handle) {
        if (handle === 0) {
            min.value = Math.round(values[0]);
        }
        if (handle === 1) {
            max.value = Math.round(values[1]);
        }
    })
    rangePrice.on('end', function(values, handle){
        min.dispatchEvent(new Event ('change'));
    })
}
if (sliderCapacity) {
    const min = document.getElementById('minCapacity');
    const max = document.getElementById('maxCapacity');

    const rangeCapacity = noUiSlider.create(sliderCapacity, {
        start: [0,Math.ceil(max.value ,10)],
        connect: true,
        range: {
            'min': 0,
            'max': Math.ceil(max.value,10),
        }
    });

    rangeCapacity.on('slide', function (values, handle) {
        if (handle === 0) {
            min.value = Math.round(values[0]);
        }
        if (handle === 1) {
            max.value = Math.round(values[1]);
        }
    })
    rangeCapacity.on('end', function(values, handle){
        min.dispatchEvent(new Event ('change'));
    })
}
