/************************* JS navbar && footer ***************************/
window.onload = footer;
window.onresize = footer;

function footer() {
    let withWindow = parseInt(window.innerWidth);
    let footer = document.querySelectorAll('.select div');

    if (withWindow > 992) {
        footer.forEach(element => {
            element.classList.remove('col-12', 'text-center');
        });
    }
    if (withWindow <= 992) {
        footer.forEach(element => {
            element.classList.add('col-12', 'text-center');
        });
    }
}