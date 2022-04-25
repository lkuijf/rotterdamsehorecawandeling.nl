const anchors = document.querySelectorAll('.anchorPoint');
const backImages = document.querySelectorAll('.backgroundImage');
const buttons = document.querySelectorAll('#mNav ul li a');
const bodyRect = document.body.getBoundingClientRect();
let anchorPositions = [];
backImages[0].classList.add("activeImage"); // on load, set first image as active

anchors.forEach(el => {
    var rect = el.getBoundingClientRect();
    offset = rect.top - bodyRect.top;
    anchorPositions.push(offset);
});

window.addEventListener("scroll",debounce(function(e){
    let scrollPos = this.scrollY;
    let viewingSection = 0;
    let activeButton = 0;
    anchorPositions.forEach((val, i) => {
        let anchorOffset = val - 600;
        if(anchorOffset < 0) anchorOffset = 0;
        if(scrollPos >= anchorOffset) viewingSection = i;
        if(scrollPos+1 >= val) activeButton = i;
    });
    backImages.forEach((imgEl, i) => {
        if(i == viewingSection) {
            imgEl.classList.add("activeImage");
        } else {
            imgEl.classList.remove("activeImage");
        }
    });
    buttons.forEach((btnEl, i) => {
        if(i == activeButton) {
            btnEl.classList.add("activeButton");
        } else {
            btnEl.classList.remove("activeButton");
        }
    });
}));

function debounce(func){
    var timer;
    return function(event){
        if(timer) clearTimeout(timer);
        timer = setTimeout(func,50,event);
    };
}

function initMsgHolder() {
    const msg = document.createElement("div");
    msg.setAttribute("id", "msg");
    const body = document.getElementsByTagName("body")[0];
    body.appendChild(msg);
}
function showMessage(type, fontAwesomeClassname, message, timeout = 3000, elementHeight = 80) {
    const successMsgEl = document.querySelector('#msg');
    successMsgEl.classList = '';
    successMsgEl.classList.add(type);
    successMsgEl.innerHTML = '<div><i class="' + fontAwesomeClassname + '"></i> ' + message + '</div>';
    successMsgEl.style.height = elementHeight +'px';
    successMsgEl.style.top = '-' + elementHeight + 'px';
    const originalTop = successMsgEl.style.top;
    setTimeout(function() { successMsgEl.style.top = 0; }, 50)
    setTimeout(function() { successMsgEl.style.top = originalTop; }, timeout);
}