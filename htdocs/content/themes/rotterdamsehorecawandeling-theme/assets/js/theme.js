const wtBlocks = document.querySelectorAll('.wtBlock');
const anchors = document.querySelectorAll('.wtanchor');
const toTopBtn = document.querySelector('#toTop');
const burgerCheckbox_selector = '#burger-check';
const burgerCheckbox = document.querySelector(burgerCheckbox_selector);
const burgerMenuButtons = document.querySelectorAll(burgerCheckbox_selector + ' ~ div ul li a');
const bodyRect = document.body.getBoundingClientRect();
let anchorPositions = [];
anchors.forEach(el => {
    var rect = el.getBoundingClientRect();
    offset = rect.top - bodyRect.top;
    anchorPositions.push(offset);
});


renderView(true);


function setActiveImage(imageIndex = 0) {
    wtBlocks.forEach((block, i) => {
        let classToAdd = 'hiddenImage';
        if(i == imageIndex) {
            block.querySelector('.wtbImage').classList.remove(classToAdd); // Do not hide images with CSS => JS could be disabled.
        } else {
            block.querySelector('.wtbImage').classList.add(classToAdd); // Do not hide images with CSS => JS could be disabled.
        }
    });
}

function getSectionInViewport(positionOfScroll) {
    let shownSection = false;
    const firstBlock = document.querySelector('#primary #main article .entry-content :first-child'); // "#primary #main article" just to be sure
    if(positionOfScroll <= anchorPositions[0]) {
        // When no wtBlock in view, show no image. UNLESS .wtBlock element is the first-child of .entry-content (no other Gutenberg blocks are present), than show the first image.
        if(firstBlock.className == 'wtBlock') shownSection = 0;
        else shownSection = -1;
    }
    if(positionOfScroll > anchorPositions[(anchorPositions.length - 1)]) shownSection = (anchorPositions.length - 1); // When outside of all wtBlocks, just set the last image to display
    if(shownSection === false) { // we are somewhere within the sections, search for correct one
        anchorPositions.forEach((anchorPos, i) => {
            if(positionOfScroll > anchorPos && positionOfScroll <= anchorPositions[i + 1]) {
                shownSection = i;
            }
        });
    }

    return shownSection;
}

function renderView(onPageLoad = false) {
    if(wtBlocks.length) { // only when parallax blocks present on page
        let blockToMeasure = wtBlocks[0];
        if(wtBlocks[1]) blockToMeasure = wtBlocks[1]; // first block has more padding, for better showing of first image. Taking the second for better representation.
        let wtbContentPaddingTop = parseInt(window.getComputedStyle(blockToMeasure.querySelector('.wtbContent'), null).getPropertyValue('padding-top'));
        let scrollPos = this.scrollY;
        let sectionInViewport = getSectionInViewport(scrollPos+wtbContentPaddingTop);
        setActiveImage(sectionInViewport);
    }
    if(onPageLoad) {
        // set a delay on the opacity fade effect. On page load this causes strange behavior of the images (on page load).
        setTimeout(() => {
            wtBlocks.forEach((block, i) => {
                block.querySelector('.wtbImage').classList.add('wtbImgTransition');
            });
        }, '500');
    }
}

/***** To Top Button *************************/
toTopBtn.addEventListener('click', (e) => {
    e.preventDefault();
    window.scrollTo(0, 0);
});
if(window.scrollY > 800) {
    toTopBtn.classList.add('show');
}
/*********************************************/

/***** Hide burger-menu when item is clicked ************/
burgerMenuButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        burgerCheckbox.checked = false;
        let event = new Event('change');
        burgerCheckbox.dispatchEvent(event);
    });
});
/********************************************************/

window.addEventListener("scroll",
    debounce(function(e){
        let fromTop = window.scrollY;
        if(fromTop > 400) {
            toTopBtn.classList.add('show');
        } else {
            toTopBtn.classList.remove('show');
        }
        renderView();
    })
);
function debounce(func){
    var timer;
    return function(event){
        if(timer) clearTimeout(timer);
        timer = setTimeout(func,20,event);
    };
}
