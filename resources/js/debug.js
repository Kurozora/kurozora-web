document.addEventListener('livewire:navigated', () => {
    let breakpoints = {
        '': 0,
        'sm': 640,
        'md': 768,
        'lg': 1024,
        'xl': 1280,
        '2xl': 1536
    }

    // Configure button
    let tailwindCssScreenButton = document.createElement('button')
    tailwindCssScreenButton.setAttribute('class', 'fixed pt-2 pr-2 pb-2 pl-2 bg-black text-sm text-white opacity-25 hover:opacity-100')
    tailwindCssScreenButton.setAttribute('style', 'z-index: 999999999;')
    tailwindCssScreenButton.onload = calculateBreakpoint()
    document.querySelector('body').appendChild(tailwindCssScreenButton)

    // MARK: - Functions
    /**
     * Calculate the current breakpoint.
     */
    function calculateBreakpoint() {
        let innerWidth = window.innerWidth

        Object.entries(breakpoints).forEach(entry => {
            const [breakpointKey, breakpointValue] = entry;

            if (breakpointValue <= innerWidth) {
                tailwindCssScreenButton.textContent = `${window.innerWidth}px`

                if (!!breakpointKey) {
                    tailwindCssScreenButton.textContent += ` | ${breakpointKey}`
                }
            }
        })
    }

    // MARK: - Events
    window.onresize = function(event) {
        calculateBreakpoint()
    }
})

// var viewFullScreen = document.querySelector("main");
// if (viewFullScreen) {
//   viewFullScreen.addEventListener("click", function() {
//     var docElm = document.documentElement;
//     if (docElm.requestFullscreen) {
//       docElm.requestFullscreen();
//     } else if (docElm.msRequestFullscreen) {
//       docElm.msRequestFullscreen();
//     } else if (docElm.mozRequestFullScreen) {
//       docElm.mozRequestFullScreen();
//     } else if (docElm.webkitRequestFullScreen) {
//       docElm.webkitRequestFullScreen();
//     }
//   })
// }
