document.addEventListener("DOMContentLoaded", function () {
    // Code to be executed when the DOM is ready (i.e. the document is
    // fully loaded):
    registerEventListners(); // You need to write this function...
    activateMenu();
});

function registerEventListners() {
    const popup_images = document.getElementsByClassName('image-thumbnail');
    for (let i = 0; i < popup_images.length; i++) {
        popup_images[i].addEventListener('click', function () {
            showPopup(this.src);
        });
    }
}

function showPopup(imageSrc) {

    const displays = document.getElementsByClassName('img-popup');
    if (displays.length > 0) {
        for (let i = 0; i < displays.length; i++) {
            displays[i].remove();
        }
    }
    const popupimg = document.createElement('img');
    popupimg.src = imageSrc.replace("small", "large");
    popupimg.classList.add('img-popup');
    popupimg.addEventListener('click', function () {
        popupimg.remove();
    });
    document.body.appendChild(popupimg);
}

function activateMenu() {
    const navLinks = document.querySelectorAll('nav a');
    navLinks.forEach(link => {
        if (link.href === location.href) {
            link.classList.add('active');
        }
    })
}
