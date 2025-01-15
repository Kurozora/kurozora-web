<div
    x-data="{
        show: @entangle('showColorPicker').live,
        state: @entangle('state').live,
        selectedElement: @entangle('selectedElement').live,
        colorPicker: null,
        colorPickerColor: '#FF9300',
        initializeColorPicker() {
            const colorPicker = new iro.ColorPicker('#colorPicker', {
                layout: [
                    {
                        component: iro.ui.Box,
                    },
                    {
                        component: iro.ui.Slider,
                        options: {
                            sliderType: 'red'
                        }
                    },
                    {
                        component: iro.ui.Slider,
                        options: {
                            sliderType: 'green'
                        }
                    },
                    {
                        component: iro.ui.Slider,
                        options: {
                            sliderType: 'blue'
                        }
                    },
                    {
                        component: iro.ui.Slider,
                        options: {
                            sliderType: 'alpha'
                        }
                    }
                ],
                color: this.colorPickerColor
            });

            colorPicker.on(['color:change'], this.handleColorPickerColorChange);
            this.colorPicker = colorPicker;
        },
        handleColorPickerColorChange(color) {
            document.querySelectorAll('[x-data]').forEach(el => {
                if (el._x_dataStack[0]['selectedElement']) {
                    self = el._x_dataStack[0];
                    selectedElement = self['selectedElement'];
                    if (selectedElement) {
                        let colorPickerInput = document.querySelector('#' + selectedElement);
                        colorPickerInput.value = color.hex8String;
                        colorPickerInput.dispatchEvent(new Event('input'));
                    }
                }
            });
        },
        updateColorPicker(value) {
            this.colorPicker.color.hex8String = value;
        },
    }"
    x-init="$nextTick(() => {
        @this.set('state.global_background_color', '#353a50');
        @this.set('state.global_text_color', '#B7B9C1');
        @this.set('state.global_bar_title_text_color', '#ffffff');
        @this.set('state.table_view_cell_title_text_color', '#ffffff');
        @this.set('state.global_tint_color', '#FF9300');
        @this.set('state.global_bar_tint_color', '#2a2e43');
        @this.set('state.ui_status_bar_style', '#ffffff');

        initializeColorPicker();
    })"
    x-on:keydown.escape.window="show = false; selectedElement = null"
>
    <style scoped>
        #iphoneX {
            max-width: 100%;
            display: block;
            margin: 0 auto;
        }

        .eyedropper:hover {
            cursor: pointer;
        }
    </style>

    <!-- iPhone SVG  -->
    <svg id="iphoneX" width="469px" height="921px" viewBox="0 0 469 921" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <rect id="path-1" x="0.598360656" y="0" width="407" height="843" rx="50"></rect>
            <filter x="-25.1%" y="-6.2%" width="150.1%" height="124.2%" filterUnits="objectBoundingBox" id="filter-3">
                <feGaussianBlur stdDeviation="15" in="SourceAlpha" result="shadowBlurInner1"></feGaussianBlur>
                <feOffset dx="0" dy="-18" in="shadowBlurInner1" result="shadowOffsetInner1"></feOffset>
                <feComposite in="shadowOffsetInner1" in2="SourceAlpha" operator="arithmetic" k2="-1" k3="1" result="shadowInnerInner1"></feComposite>
                <feColorMatrix values="0 0 0 0 1   0 0 0 0 1   0 0 0 0 1  0 0 0 0.12 0" type="matrix" in="shadowInnerInner1"></feColorMatrix>
            </filter>
            <rect id="path-4" x="17.5983607" y="19" width="371" height="807" rx="30"></rect>
            <rect id="path-6" x="0" y="0" width="376" height="49"></rect>
            <filter x="-0.1%" y="-1.5%" width="100.1%" height="102.0%" filterUnits="objectBoundingBox" id="filter-7">
                <feOffset dx="0" dy="-0.5" in="SourceAlpha" result="shadowOffsetOuter1"></feOffset>
                <feComposite in="shadowOffsetOuter1" in2="SourceAlpha" operator="out" result="shadowOffsetOuter1"></feComposite>
                <feColorMatrix values="0 0 0 0 1   0 0 0 0 1   0 0 0 0 1  0 0 0 0.15 0" type="matrix" in="shadowOffsetOuter1"></feColorMatrix>
            </filter>
            <rect id="path-8" x="0" y="0" width="376" height="88"></rect>
            <filter x="-0.1%" y="-0.3%" width="100.1%" height="101.1%" filterUnits="objectBoundingBox" id="filter-9">
                <feOffset dx="0" dy="0.5" in="SourceAlpha" result="shadowOffsetOuter1"></feOffset>
                <feComposite in="shadowOffsetOuter1" in2="SourceAlpha" operator="out" result="shadowOffsetOuter1"></feComposite>
                <feColorMatrix values="0 0 0 0 1   0 0 0 0 1   0 0 0 0 1  0 0 0 0.16 0" type="matrix" in="shadowOffsetOuter1"></feColorMatrix>
            </filter>
            <rect id="path-10" x="0" y="0" width="376" height="50"></rect>
            <filter x="-0.1%" y="-1.5%" width="100.1%" height="102.0%" filterUnits="objectBoundingBox" id="filter-11">
                <feOffset dx="0" dy="-0.5" in="SourceAlpha" result="shadowOffsetOuter1"></feOffset>
                <feComposite in="shadowOffsetOuter1" in2="SourceAlpha" operator="out" result="shadowOffsetOuter1"></feComposite>
                <feColorMatrix values="0 0 0 0 1   0 0 0 0 1   0 0 0 0 1  0 0 0 0.15 0" type="matrix" in="shadowOffsetOuter1"></feColorMatrix>
            </filter>
        </defs>
        <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
            <g id="Artboard" transform="translate(0.000000, -21.000000)">
                <g id="dark" transform="translate(31.401639, 48.000000)">
                    <g id="base">
                        <use fill="black" fill-opacity="1" filter="url(#filter-2)" xlink:href="#path-1"></use>
                        <use fill="#070707" fill-rule="evenodd" xlink:href="#path-1"></use>
                        <use fill="black" fill-opacity="1" filter="url(#filter-3)" xlink:href="#path-1"></use>
                    </g>
                    <mask id="mask-5" fill="white">
                        <use xlink:href="#path-4"></use>
                    </mask>
                    <use id="background" fill="#1D1D1D" xlink:href="#path-4"></use>
                    <g id="iPhone-X" mask="url(#mask-5)">
                        <g transform="translate(15.598361, 14.000000)">
                            <rect id="Rectangle" x-bind:fill="state.global_background_color" fill-rule="evenodd" x="0" y="0" width="376" height="812"></rect>

                            <!-- Eyedropper global_background_color -->
                            <g class="eyedropper" x-on:click="show = !show; @this.set('selectedElement', 'global_background_color'); updateColorPicker(state.global_background_color)" transform="translate(200, 400)" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <circle id="Oval" fill="#445CD3" cx="10" cy="10" r="10"></circle>
                                <path d="M13.948,7.552 C13.607,7.852 13.5715,8.371 13.8715,8.714 L13.252,9.2555 L10.544,6.1565 L11.1635,5.6155 C11.463,5.9575 11.9825,5.992 12.3235,5.6925 L13.9465,4.3045 C14.1815,4.101 14.4705,4 14.759,4 C15.449,4 16,4.564 16,5.2355 C16,5.6005 15.839,5.932 15.573,6.1645 L13.948,7.552 Z M10.4765,9.5 L9.2265,9.5 L11.0075,7.9385 L10.465,7.3185 L6.7955,10.512 C6.201,11.0285 6.6115,11.4275 6.1215,12.1095 C6.055,12.2025 6.0165,12.2955 6.006,12.3845 C5.973,12.6545 6.1685,12.882 6.4145,12.9125 C6.5115,12.924 6.6195,12.905 6.7225,12.844 C7.4975,12.388 7.8115,12.9025 8.4205,12.3705 L12.0905,9.1785 L11.55,8.558 L10.4765,9.5 Z M5.9235,13.25 C5.656,14.2765 5,14.4175 5,15.092 C5,15.5935 5.417,16 5.9235,16 C6.43,16 6.8405,15.5935 6.8405,15.092 C6.8405,14.4175 6.191,14.2765 5.9235,13.25 Z" id="Shape" fill="#FFFFFF" fill-rule="nonzero"></path>
                            </g>
                            <!-- /Eyedropper global_background_color -->

                            <g id="Forum-Cell" stroke-width="1" fill="none" fill-rule="evenodd" transform="translate(1.000000, 137.000000)">
                                <rect id="Rectangle" x="0" y="0" width="375" height="96"></rect>
                                <path d="M15,95.5 L361,95.5" id="Line" stroke="#979797" stroke-width="0.5" stroke-linecap="square"></path>
                                <text id="" font-size="20" font-weight="normal" letter-spacing="0.583194435" fill="#FFFFFF" fill-opacity="0.64">
                                    <tspan x="339" y="89" class="fas"></tspan>
                                </text>
                                <text id="" font-size="20" font-weight="normal" letter-spacing="0.583194435" fill="#FFFFFF" fill-opacity="0.64">
                                    <tspan x="307" y="89" class="fas"></tspan>
                                </text>
                                <text id="" font-size="22" font-weight="normal" letter-spacing="0.641513944" fill="#FFFFFF" fill-opacity="0.64">
                                    <tspan x="272" y="90" class="fas"></tspan>
                                </text>
                                <text id="-2.2K-·--3K-·--10" font-size="13" font-weight="normal" letter-spacing="0.266" fill="#FFFFFF" fill-opacity="0.5">
                                    <tspan x="15" y="84" class="fas"> 2.2K </tspan>
                                    <tspan x="72.6826875" y="84" class="fas">  3K </tspan>
                                    <tspan x="125.579844" y="84" class="fas">  10hrs</tspan>
                                </text>
                                <text id="Commodi-a-quisquam-v" font-family="ArialMT, Arial" font-size="15" font-weight="normal" letter-spacing="0.323000014" x-bind:fill="state.table_view_cell_title_text_color">
                                    <tspan x="31" y="24">Commodi a quisquam voluptatem repellendus.</tspan>
                                </text>

                                <!-- Eyedropper table_view_cell_title_text_color -->
                                <g class="eyedropper" x-on:click="show = !show; @this.set('selectedElement', 'table_view_cell_title_text_color'); updateColorPicker(state.table_view_cell_title_text_color)" transform="translate(340, 10)" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <circle id="Oval" fill="#445CD3" cx="10" cy="10" r="10"></circle>
                                    <path d="M13.948,7.552 C13.607,7.852 13.5715,8.371 13.8715,8.714 L13.252,9.2555 L10.544,6.1565 L11.1635,5.6155 C11.463,5.9575 11.9825,5.992 12.3235,5.6925 L13.9465,4.3045 C14.1815,4.101 14.4705,4 14.759,4 C15.449,4 16,4.564 16,5.2355 C16,5.6005 15.839,5.932 15.573,6.1645 L13.948,7.552 Z M10.4765,9.5 L9.2265,9.5 L11.0075,7.9385 L10.465,7.3185 L6.7955,10.512 C6.201,11.0285 6.6115,11.4275 6.1215,12.1095 C6.055,12.2025 6.0165,12.2955 6.006,12.3845 C5.973,12.6545 6.1685,12.882 6.4145,12.9125 C6.5115,12.924 6.6195,12.905 6.7225,12.844 C7.4975,12.388 7.8115,12.9025 8.4205,12.3705 L12.0905,9.1785 L11.55,8.558 L10.4765,9.5 Z M5.9235,13.25 C5.656,14.2765 5,14.4175 5,15.092 C5,15.5935 5.417,16 5.9235,16 C6.43,16 6.8405,15.5935 6.8405,15.092 C6.8405,14.4175 6.191,14.2765 5.9235,13.25 Z" id="Shape" fill="#FFFFFF" fill-rule="nonzero"></path>
                                </g>
                                <!-- /Eyedropper table_view_cell_title_text_color -->

                                <text id="Totam-corporis-asper" font-family="ArialMT, Arial" font-size="12" font-weight="normal" letter-spacing="0.349916697"
                                      x-bind:fill="state.global_text_color">
                                    <tspan x="31" y="43">Totam corporis aspernatur aut temporibus autem maxime.</tspan>
                                    <tspan x="31" y="57">Consequatur reprehendrit non et cupiditate i…</tspan>
                                </text>

                                <!-- Eyedropper global_text_color -->
                                <g class="eyedropper" x-on:click="show = !show; @this.set('selectedElement', 'global_text_color'); updateColorPicker(state.global_text_color)" transform="translate(280, 45)" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <circle id="Oval" fill="#445CD3" cx="10" cy="10" r="10"></circle>
                                    <path d="M13.948,7.552 C13.607,7.852 13.5715,8.371 13.8715,8.714 L13.252,9.2555 L10.544,6.1565 L11.1635,5.6155 C11.463,5.9575 11.9825,5.992 12.3235,5.6925 L13.9465,4.3045 C14.1815,4.101 14.4705,4 14.759,4 C15.449,4 16,4.564 16,5.2355 C16,5.6005 15.839,5.932 15.573,6.1645 L13.948,7.552 Z M10.4765,9.5 L9.2265,9.5 L11.0075,7.9385 L10.465,7.3185 L6.7955,10.512 C6.201,11.0285 6.6115,11.4275 6.1215,12.1095 C6.055,12.2025 6.0165,12.2955 6.006,12.3845 C5.973,12.6545 6.1685,12.882 6.4145,12.9125 C6.5115,12.924 6.6195,12.905 6.7225,12.844 C7.4975,12.388 7.8115,12.9025 8.4205,12.3705 L12.0905,9.1785 L11.55,8.558 L10.4765,9.5 Z M5.9235,13.25 C5.656,14.2765 5,14.4175 5,15.092 C5,15.5935 5.417,16 5.9235,16 C6.43,16 6.8405,15.5935 6.8405,15.092 C6.8405,14.4175 6.191,14.2765 5.9235,13.25 Z" id="Shape" fill="#FFFFFF" fill-rule="nonzero"></path>
                                </g>
                                <!-- /Eyedropper global_text_color -->

                                <text id="" font-size="14" font-weight="normal" letter-spacing="0.266000032" x-bind:fill="state.table_view_cell_title_text_color">
                                    <tspan x="15" y="24" class="fas"></tspan>
                                </text>
                            </g>
                            <g id="Group" stroke-width="1" fill="none" fill-rule="evenodd" transform="translate(0.000000, 88.000000)">
                                <g id="background">
                                    <use fill="black" fill-opacity="1" filter="url(#filter-7)" xlink:href="#path-6"></use>
                                    <use fill-opacity="0.72" x-bind:fill="state.global_bar_tint_color" fill-rule="evenodd" xlink:href="#path-6"></use>
                                </g>
                                <rect id="Rectangle" x-bind:fill="state.global_tint_color" x="16" y="43" width="48" height="2" rx="1"></rect>
                                <text id="Anime" font-family="ArialMT, Arial" font-size="17" font-weight="normal" line-spacing="25" letter-spacing="0.323000014" x-bind:fill="state.global_tint_color">
                                    <tspan x="16" y="23">Anime</tspan>
                                </text>
                                <text id="Real-Life" font-family="ArialMT, Arial" font-size="17" font-weight="normal" line-spacing="25" letter-spacing="0.323000014" x-bind:fill="state.global_tint_color" fill-opacity="0.5">
                                    <tspan x="90" y="23">Real Life</tspan>
                                </text>
                                <text id="Memes" font-family="ArialMT, Arial" font-size="17" font-weight="normal" line-spacing="25" letter-spacing="0.323000014" x-bind:fill="state.global_tint_color" fill-opacity="0.5">
                                    <tspan x="181" y="23">Memes</tspan>
                                </text>
                                <text id="Art-Showcase" font-family="ArialMT, Arial" font-size="17" font-weight="normal" line-spacing="25" letter-spacing="0.323000014" x-bind:fill="state.global_tint_color" fill-opacity="0.5">
                                    <tspan x="259" y="23">Art Showcase</tspan>
                                </text>
                            </g>
                            <g id="Bars-/-Navigation-Bar-/-iPhone---Compact-/-Dark---Default" fill="none">
                                <g id="Background">
                                    <use fill="black" fill-opacity="1" filter="url(#filter-9)" xlink:href="#path-8"></use>
                                    <use fill-opacity="0.72" x-bind:fill="state.global_bar_tint_color" fill-rule="evenodd" xlink:href="#path-8"></use>
                                </g>
                                <g id="Bars-/-Navigation-Bar-/-x-/-Right-Combinations-/-Dark---Bar-Button-Item" transform="translate(206.549333, 44.000000)" x-bind:fill="state.global_tint_color" fill-rule="evenodd">
                                    <g id="Glyphs-/-Bar-Button-/-Search-/-Dark" transform="translate(130.000000, 8.000000)">
                                        <path d="M6.0148,12.0972 C6.0148,10.7142 6.4848,9.4392 7.2668,8.4162 C8.2608,7.1152 9.7628,6.2212 11.4768,6.0462 C11.6808,6.0252 11.8878,6.0152 12.0978,6.0152 C13.0488,6.0152 13.9468,6.2402 14.7498,6.6312 C16.7778,7.6182 18.1828,9.6942 18.1828,12.0982 C18.1828,15.4532 15.4528,18.1832 12.0988,18.1832 C10.4278,18.1832 8.9118,17.5062 7.8118,16.4102 C7.8078,16.4062 7.8028,16.4042 7.7988,16.3982 C7.7918,16.3922 7.7868,16.3862 7.7808,16.3792 C6.6908,15.2792 6.0148,13.7652 6.0148,12.0972 L6.0148,12.0972 Z M22.9718,21.5392 L17.7788,16.3442 C17.8868,16.1992 17.9918,16.0502 18.0888,15.8972 C18.7878,14.7982 19.1988,13.4972 19.1988,12.0982 C19.1988,8.1782 16.0198,5.0002 12.0988,5.0002 C9.6848,5.0002 7.5568,6.2072 6.2738,8.0482 C5.4728,9.1972 4.9998,10.5922 4.9998,12.0972 C4.9998,16.0202 8.1798,19.1972 12.0978,19.1972 C13.6928,19.1972 15.1598,18.6662 16.3438,17.7782 L21.5388,22.9722 L22.9718,21.5392 Z" id="search"></path>
                                    </g>
                                </g>
                                <text id="Title" font-size="17" font-weight="500" line-spacing="22" letter-spacing="-0.409999996" x-bind:fill="state.global_bar_title_text_color" wire:model.live="state.global_bar_title_text_color">
                                    <tspan x="157.919453" y="71">Forums</tspan>
                                </text>

                                <!-- Eyedropper global_bar_title_text_color -->
                                <g class="eyedropper" x-on:click="show = !show; @this.set('selectedElement', 'global_bar_title_text_color'); updateColorPicker(state.global_bar_title_text_color)" transform="translate(215, 60)" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <circle id="Oval" fill="#445CD3" cx="10" cy="10" r="10"></circle>
                                    <path d="M13.948,7.552 C13.607,7.852 13.5715,8.371 13.8715,8.714 L13.252,9.2555 L10.544,6.1565 L11.1635,5.6155 C11.463,5.9575 11.9825,5.992 12.3235,5.6925 L13.9465,4.3045 C14.1815,4.101 14.4705,4 14.759,4 C15.449,4 16,4.564 16,5.2355 C16,5.6005 15.839,5.932 15.573,6.1645 L13.948,7.552 Z M10.4765,9.5 L9.2265,9.5 L11.0075,7.9385 L10.465,7.3185 L6.7955,10.512 C6.201,11.0285 6.6115,11.4275 6.1215,12.1095 C6.055,12.2025 6.0165,12.2955 6.006,12.3845 C5.973,12.6545 6.1685,12.882 6.4145,12.9125 C6.5115,12.924 6.6195,12.905 6.7225,12.844 C7.4975,12.388 7.8115,12.9025 8.4205,12.3705 L12.0905,9.1785 L11.55,8.558 L10.4765,9.5 Z M5.9235,13.25 C5.656,14.2765 5,14.4175 5,15.092 C5,15.5935 5.417,16 5.9235,16 C6.43,16 6.8405,15.5935 6.8405,15.092 C6.8405,14.4175 6.191,14.2765 5.9235,13.25 Z" id="Shape" fill="#FFFFFF" fill-rule="nonzero"></path>
                                </g>
                                <!-- /Eyedropper global_bar_title_text_color -->

                                <!-- Eyedropper global_bar_tint_color -->
                                <g class="eyedropper" x-on:click="show = !show; @this.set('selectedElement', 'global_bar_tint_color'); updateColorPicker(state.global_bar_tint_color)" transform="translate(265, 60)" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <circle id="Oval" fill="#445CD3" cx="10" cy="10" r="10"></circle>
                                    <path d="M13.948,7.552 C13.607,7.852 13.5715,8.371 13.8715,8.714 L13.252,9.2555 L10.544,6.1565 L11.1635,5.6155 C11.463,5.9575 11.9825,5.992 12.3235,5.6925 L13.9465,4.3045 C14.1815,4.101 14.4705,4 14.759,4 C15.449,4 16,4.564 16,5.2355 C16,5.6005 15.839,5.932 15.573,6.1645 L13.948,7.552 Z M10.4765,9.5 L9.2265,9.5 L11.0075,7.9385 L10.465,7.3185 L6.7955,10.512 C6.201,11.0285 6.6115,11.4275 6.1215,12.1095 C6.055,12.2025 6.0165,12.2955 6.006,12.3845 C5.973,12.6545 6.1685,12.882 6.4145,12.9125 C6.5115,12.924 6.6195,12.905 6.7225,12.844 C7.4975,12.388 7.8115,12.9025 8.4205,12.3705 L12.0905,9.1785 L11.55,8.558 L10.4765,9.5 Z M5.9235,13.25 C5.656,14.2765 5,14.4175 5,15.092 C5,15.5935 5.417,16 5.9235,16 C6.43,16 6.8405,15.5935 6.8405,15.092 C6.8405,14.4175 6.191,14.2765 5.9235,13.25 Z" id="Shape" fill="#FFFFFF" fill-rule="nonzero"></path>
                                </g>
                                <!-- /Eyedropper global_bar_tint_color -->

                                <g id="Bars-/-Navigation-Bar-/-x-/-Left-Combinations-/-Dark---Back-Button" transform="translate(0.000000, 44.000000)" x-bind:fill="state.global_tint_color">
                                    <text id="Label" font-size="17" font-weight="normal" line-spacing="22" letter-spacing="-0.408">
                                        <tspan x="27" y="26">Kurozora</tspan>
                                    </text>
                                    <path d="M18.0371349,31.5826673 L8.79215185,22.4458042 C8.40261605,22.0611888 8.40261605,21.4398102 8.79215185,21.0541958 L18.0371349,11.9173327 C18.5994648,11.3608891 19.5143745,11.3608891 20.0777032,11.9173327 C20.6400331,12.4737762 20.6400331,13.3768731 20.0777032,13.9333167 L12.1691276,21.7504995 L20.0777032,29.5656843 C20.6400331,30.1231269 20.6400331,31.0262238 20.0777032,31.5826673 C19.5143745,32.1391109 18.5994648,32.1391109 18.0371349,31.5826673" id="Chevron" fill-rule="evenodd"></path>
                                </g>
                            </g>
                            <g id="Bars-/-Tab-Bar-/-Compact-/-Dark---5-Tabs" fill="none" transform="translate(0.000000, 762.000000)">
                                <g id="Background">
                                    <use fill="black" fill-opacity="1" filter="url(#filter-11)" xlink:href="#path-10"></use>
                                    <use fill-opacity="0.72" x-bind:fill="state.global_bar_tint_color" fill-rule="evenodd" xlink:href="#path-10"></use>
                                </g>
                                <g id="Bars-/-Tab-Bar-/-x-/-Tab-Button-/-Inactive" transform="translate(310.200000, 0.000000)" fill="#8E8E93">
                                    <text id="Label" font-size="10" font-weight="400" letter-spacing="-0.2411765">
                                        <tspan x="13.2191178" y="44">Profile</tspan>
                                    </text>
                                    <g id="Glyphs-/-Tab-Bar-/-Favorite-/-Disabled" transform="translate(4.000000, 4.000000)" fill-rule="evenodd">
                                        <path d="M24,2.125 C24.4415,2.125 24.883,2.374 25.0465,2.8715 L27.4805,10.281 C27.6265,10.7255 28.0495,11.027 28.527,11.027 L36.4295,11.027 C37.491,11.027 37.936,12.354 37.08,12.969 L30.6665,17.578 C30.285,17.8515 30.126,18.333 30.2705,18.7735 L32.7155,26.216 C32.9635,26.97 32.3505,27.625 31.665,27.625 C31.4485,27.625 31.2245,27.5595 31.0185,27.4115 L24.6505,22.8355 C24.457,22.6965 24.2285,22.627 24,22.627 C23.7715,22.627 23.543,22.6965 23.3495,22.8355 L16.9815,27.4115 C16.7755,27.5595 16.5515,27.625 16.335,27.625 C15.6495,27.625 15.0365,26.97 15.2845,26.216 L17.7295,18.7735 C17.874,18.333 17.715,17.8515 17.3335,17.578 L10.92,12.969 C10.064,12.354 10.509,11.027 11.5705,11.027 L19.473,11.027 C19.9505,11.027 20.3735,10.7255 20.5195,10.281 L22.9535,2.8715 C23.117,2.374 23.5585,2.125 24,2.125 Z" id="Combined-Shape"></path>
                                    </g>
                                </g>
                                <g id="Bars-/-Tab-Bar-/-x-/-Tab-Button-/-Inactive" transform="translate(236.175000, 0.000000)" fill="#8E8E93">
                                    <text id="Label" font-size="10" font-weight="400" letter-spacing="-0.2411765">
                                        <tspan x="-1.17942306" y="44">Notifications</tspan>
                                    </text>
                                    <g id="Glyphs-/-Tab-Bar-/-Favorite-/-Disabled" transform="translate(4.000000, 4.000000)" fill-rule="evenodd">
                                        <path d="M24,2.125 C24.4415,2.125 24.883,2.374 25.0465,2.8715 L27.4805,10.281 C27.6265,10.7255 28.0495,11.027 28.527,11.027 L36.4295,11.027 C37.491,11.027 37.936,12.354 37.08,12.969 L30.6665,17.578 C30.285,17.8515 30.126,18.333 30.2705,18.7735 L32.7155,26.216 C32.9635,26.97 32.3505,27.625 31.665,27.625 C31.4485,27.625 31.2245,27.5595 31.0185,27.4115 L24.6505,22.8355 C24.457,22.6965 24.2285,22.627 24,22.627 C23.7715,22.627 23.543,22.6965 23.3495,22.8355 L16.9815,27.4115 C16.7755,27.5595 16.5515,27.625 16.335,27.625 C15.6495,27.625 15.0365,26.97 15.2845,26.216 L17.7295,18.7735 C17.874,18.333 17.715,17.8515 17.3335,17.578 L10.92,12.969 C10.064,12.354 10.509,11.027 11.5705,11.027 L19.473,11.027 C19.9505,11.027 20.3735,10.7255 20.5195,10.281 L22.9535,2.8715 C23.117,2.374 23.5585,2.125 24,2.125 Z" id="Combined-Shape"></path>
                                    </g>
                                </g>
                                <g id="Bars-/-Tab-Bar-/-x-/-Tab-Button-/-On-Dark---Active" transform="translate(159.800000, 0.000000)" x-bind:fill="state.global_tint_color">
                                    <text id="Label" font-size="10" font-weight="400" letter-spacing="-0.2411765">
                                        <tspan x="10.6571233" y="44">Forums</tspan>
                                    </text>
                                    <g id="Glyphs-/-Tab-Bar-/-Favorite-/-Dark" transform="translate(4.000000, 4.000000)" fill-rule="evenodd">
                                        <path d="M24,2.125 C24.4415,2.125 24.883,2.374 25.0465,2.8715 L27.4805,10.281 C27.6265,10.7255 28.0495,11.027 28.527,11.027 L36.4295,11.027 C37.491,11.027 37.936,12.354 37.08,12.969 L30.6665,17.578 C30.285,17.8515 30.126,18.333 30.2705,18.7735 L32.7155,26.216 C32.9635,26.97 32.3505,27.625 31.665,27.625 C31.4485,27.625 31.2245,27.5595 31.0185,27.4115 L24.6505,22.8355 C24.457,22.6965 24.2285,22.627 24,22.627 C23.7715,22.627 23.543,22.6965 23.3495,22.8355 L16.9815,27.4115 C16.7755,27.5595 16.5515,27.625 16.335,27.625 C15.6495,27.625 15.0365,26.97 15.2845,26.216 L17.7295,18.7735 C17.874,18.333 17.715,17.8515 17.3335,17.578 L10.92,12.969 C10.064,12.354 10.509,11.027 11.5705,11.027 L19.473,11.027 C19.9505,11.027 20.3735,10.7255 20.5195,10.281 L22.9535,2.8715 C23.117,2.374 23.5585,2.125 24,2.125 Z" id="Combined-Shape"></path>
                                    </g>

                                    <!-- Eyedropper global_tint_color -->
                                    <g class="eyedropper" x-on:click="show = !show; @this.set('selectedElement', 'global_tint_color'); updateColorPicker(state.global_tint_color)" transform="translate(40, 0)" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                        <circle id="Oval" fill="#445CD3" cx="10" cy="10" r="10"></circle>
                                        <path d="M13.948,7.552 C13.607,7.852 13.5715,8.371 13.8715,8.714 L13.252,9.2555 L10.544,6.1565 L11.1635,5.6155 C11.463,5.9575 11.9825,5.992 12.3235,5.6925 L13.9465,4.3045 C14.1815,4.101 14.4705,4 14.759,4 C15.449,4 16,4.564 16,5.2355 C16,5.6005 15.839,5.932 15.573,6.1645 L13.948,7.552 Z M10.4765,9.5 L9.2265,9.5 L11.0075,7.9385 L10.465,7.3185 L6.7955,10.512 C6.201,11.0285 6.6115,11.4275 6.1215,12.1095 C6.055,12.2025 6.0165,12.2955 6.006,12.3845 C5.973,12.6545 6.1685,12.882 6.4145,12.9125 C6.5115,12.924 6.6195,12.905 6.7225,12.844 C7.4975,12.388 7.8115,12.9025 8.4205,12.3705 L12.0905,9.1785 L11.55,8.558 L10.4765,9.5 Z M5.9235,13.25 C5.656,14.2765 5,14.4175 5,15.092 C5,15.5935 5.417,16 5.9235,16 C6.43,16 6.8405,15.5935 6.8405,15.092 C6.8405,14.4175 6.191,14.2765 5.9235,13.25 Z" id="Shape" fill="#FFFFFF" fill-rule="nonzero"></path>
                                    </g>
                                    <!-- /Eyedropper global_tint_color -->
                                </g>
                                <g id="Bars-/-Tab-Bar-/-x-/-Tab-Button-/-Inactive" transform="translate(84.600000, 0.000000)" fill="#8E8E93">
                                    <text id="Label" font-size="10" font-weight="400" letter-spacing="-0.2411765">
                                        <tspan x="11.8763443" y="44">Library</tspan>
                                    </text>
                                    <g id="Glyphs-/-Tab-Bar-/-Favorite-/-Disabled" transform="translate(4.000000, 4.000000)" fill-rule="evenodd">
                                        <path d="M24,2.125 C24.4415,2.125 24.883,2.374 25.0465,2.8715 L27.4805,10.281 C27.6265,10.7255 28.0495,11.027 28.527,11.027 L36.4295,11.027 C37.491,11.027 37.936,12.354 37.08,12.969 L30.6665,17.578 C30.285,17.8515 30.126,18.333 30.2705,18.7735 L32.7155,26.216 C32.9635,26.97 32.3505,27.625 31.665,27.625 C31.4485,27.625 31.2245,27.5595 31.0185,27.4115 L24.6505,22.8355 C24.457,22.6965 24.2285,22.627 24,22.627 C23.7715,22.627 23.543,22.6965 23.3495,22.8355 L16.9815,27.4115 C16.7755,27.5595 16.5515,27.625 16.335,27.625 C15.6495,27.625 15.0365,26.97 15.2845,26.216 L17.7295,18.7735 C17.874,18.333 17.715,17.8515 17.3335,17.578 L10.92,12.969 C10.064,12.354 10.509,11.027 11.5705,11.027 L19.473,11.027 C19.9505,11.027 20.3735,10.7255 20.5195,10.281 L22.9535,2.8715 C23.117,2.374 23.5585,2.125 24,2.125 Z" id="Combined-Shape"></path>
                                    </g>
                                </g>
                                <g id="Bars-/-Tab-Bar-/-x-/-Tab-Button-/-Inactive" transform="translate(9.400000, 0.000000)" fill="#8E8E93">
                                    <text id="Label" font-size="10" font-weight="400" letter-spacing="-0.2411765">
                                        <tspan x="14.329521" y="44">Home</tspan>
                                    </text>
                                    <g id="Glyphs-/-Tab-Bar-/-Favorite-/-Disabled" transform="translate(4.000000, 4.000000)" fill-rule="evenodd">
                                        <path d="M24,2.125 C24.4415,2.125 24.883,2.374 25.0465,2.8715 L27.4805,10.281 C27.6265,10.7255 28.0495,11.027 28.527,11.027 L36.4295,11.027 C37.491,11.027 37.936,12.354 37.08,12.969 L30.6665,17.578 C30.285,17.8515 30.126,18.333 30.2705,18.7735 L32.7155,26.216 C32.9635,26.97 32.3505,27.625 31.665,27.625 C31.4485,27.625 31.2245,27.5595 31.0185,27.4115 L24.6505,22.8355 C24.457,22.6965 24.2285,22.627 24,22.627 C23.7715,22.627 23.543,22.6965 23.3495,22.8355 L16.9815,27.4115 C16.7755,27.5595 16.5515,27.625 16.335,27.625 C15.6495,27.625 15.0365,26.97 15.2845,26.216 L17.7295,18.7735 C17.874,18.333 17.715,17.8515 17.3335,17.578 L10.92,12.969 C10.064,12.354 10.509,11.027 11.5705,11.027 L19.473,11.027 C19.9505,11.027 20.3735,10.7255 20.5195,10.281 L22.9535,2.8715 C23.117,2.374 23.5585,2.125 24,2.125 Z" id="Combined-Shape"></path>
                                    </g>
                                </g>
                            </g>
                            <g id="Bars-/-Status-Bar-/-iPhone-/-On-Dark" fill="none" transform="translate(1.000000, 0.000000)">
                                <g id="Battery" stroke-width="1" fill-rule="evenodd" transform="translate(336.000000, 17.000000)">
                                    <path d="M3.7518137,0.833333333 C2.74354052,0.833333333 2.34483914,0.910326123 1.9333052,1.13041688 C1.58587152,1.31622645 1.31619311,1.58590485 1.13038355,1.93333853 C0.910292789,2.34487247 0.8333,2.74357386 0.8333,3.75184704 L0.8333,8.24815296 C0.8333,9.25642614 0.910292789,9.65512753 1.13038355,10.0666615 C1.31619311,10.4140951 1.58587152,10.6837736 1.9333052,10.8695831 C2.34483914,11.0896739 2.74354052,11.1666667 3.7518137,11.1666667 L18.9147863,11.1666667 C19.9230595,11.1666667 20.3217609,11.0896739 20.7332948,10.8695831 C21.0807285,10.6837736 21.3504069,10.4140951 21.5362165,10.0666615 C21.7563072,9.65512753 21.8333,9.25642614 21.8333,8.24815296 L21.8333,3.75184704 C21.8333,2.74357386 21.7563072,2.34487247 21.5362165,1.93333853 C21.3504069,1.58590485 21.0807285,1.31622645 20.7332948,1.13041688 C20.3217609,0.910326123 19.9230595,0.833333333 18.9147863,0.833333333 L3.7518137,0.833333333 Z" id="Border" x-bind:fill="state.ui_status_bar_style" opacity="0.35"></path>
                                    <path d="M23.3333,4 L23.3333,8 C24.1380311,7.66122348 24.661338,6.87313328 24.661338,6 C24.661338,5.12686672 24.1380311,4.33877652 23.3333,4" id="Cap" x-bind:fill="state.ui_status_bar_style" fill-rule="nonzero" opacity="0.4"></path>
                                    <path d="M4.04255685,2.33333333 L18.6240431,2.33333333 C19.218389,2.33333333 19.4339132,2.39521708 19.6511971,2.51142181 C19.8684811,2.62762654 20.0390068,2.79815226 20.1552115,3.01543622 C20.2714163,3.23272018 20.3333,3.44824434 20.3333,4.04259018 L20.3333,7.95740982 C20.3333,8.55175566 20.2714163,8.76727982 20.1552115,8.98456378 C20.0390068,9.20184774 19.8684811,9.37237346 19.6511971,9.48857819 C19.4339132,9.60478292 19.218389,9.66666667 18.6240431,9.66666667 L4.04255685,9.66666667 C3.44821101,9.66666667 3.23268684,9.60478292 3.01540288,9.48857819 C2.79811893,9.37237346 2.62759321,9.20184774 2.51138847,8.98456378 C2.39518374,8.76727982 2.3333,8.55175566 2.3333,7.95740982 L2.3333,4.04259018 C2.3333,3.44824434 2.39518374,3.23272018 2.51138847,3.01543622 C2.62759321,2.79815226 2.79811893,2.62762654 3.01540288,2.51142181 C3.23268684,2.39521708 3.44821101,2.33333333 4.04255685,2.33333333 Z" id="Capacity" x-bind:fill="state.ui_status_bar_style" fill-rule="nonzero"></path>
                                </g>
                                <path d="M323.667069,19.6151501 C325.891802,19.6152475 328.031463,20.4693583 329.643817,22.0009506 C329.765231,22.1191953 329.959299,22.1177037 330.078879,21.9976068 L331.23949,20.8272772 C331.300039,20.7663644 331.333797,20.6838545 331.333294,20.5980054 C331.332792,20.5121564 331.29807,20.4300466 331.236813,20.3698455 C327.0049,16.3176151 320.328569,16.3176151 316.096657,20.3698455 C316.035354,20.4300019 316.000572,20.5120868 316.000007,20.5979359 C315.999442,20.6837851 316.03314,20.7663201 316.093645,20.8272772 L317.254591,21.9976068 C317.374094,22.117885 317.568312,22.1193777 317.689653,22.0009506 C319.302212,20.4692577 321.442119,19.6151445 323.667069,19.6151501 L323.667069,19.6151501 Z M323.667069,23.4227339 C324.889415,23.4226591 326.068141,23.8766124 326.97421,24.6963869 C327.09676,24.8127317 327.289808,24.8102093 327.409272,24.6907025 L328.568544,23.5203729 C328.629594,23.4589857 328.663468,23.3757088 328.662588,23.2891732 C328.661707,23.2026376 328.626146,23.120066 328.563859,23.0599317 C325.804697,20.4955232 321.531784,20.4955232 318.772622,23.0599317 C318.710297,23.1200652 318.674737,23.2026782 318.67392,23.2892418 C318.673102,23.3758053 318.707094,23.4590744 318.768272,23.5203729 L319.92721,24.6907025 C320.046673,24.8102093 320.239722,24.8127317 320.362272,24.6963869 C321.267742,23.877154 322.445531,23.4232396 323.667069,23.4227339 L323.667069,23.4227339 Z M325.894921,26.2158092 C325.956921,26.1550129 325.991064,26.0713489 325.989291,25.9845705 C325.987517,25.897792 325.949983,25.8155907 325.885551,25.7573743 C324.604891,24.6751031 322.729248,24.6751031 321.448588,25.7573743 C321.384111,25.8155439 321.346516,25.8977193 321.344679,25.9844982 C321.342841,26.0712771 321.376925,26.1549675 321.438883,26.2158092 L323.444518,28.2378044 C323.503302,28.2972294 323.583446,28.3306727 323.667069,28.3306727 C323.750693,28.3306727 323.830837,28.2972294 323.88962,28.2378044 L325.894921,26.2158092 Z" id="Wifi" x-bind:fill="state.ui_status_bar_style" fill-rule="nonzero"></path>
                                <path d="M295,24.3333337 L296,24.3333337 C296.552285,24.3333337 297,24.7810489 297,25.3333337 L297,27.3333337 C297,27.8856184 296.552285,28.3333337 296,28.3333337 L295,28.3333337 C294.447715,28.3333337 294,27.8856184 294,27.3333337 L294,25.3333337 C294,24.7810489 294.447715,24.3333337 295,24.3333337 L295,24.3333337 Z M299.666667,22.3333337 L300.666667,22.3333337 C301.218951,22.3333337 301.666667,22.7810489 301.666667,23.3333337 L301.666667,27.3333337 C301.666667,27.8856184 301.218951,28.3333337 300.666667,28.3333337 L299.666667,28.3333337 C299.114382,28.3333337 298.666667,27.8856184 298.666667,27.3333337 L298.666667,23.3333337 C298.666667,22.7810489 299.114382,22.3333337 299.666667,22.3333337 Z M304.333333,20.0000003 L305.333333,20.0000003 C305.885618,20.0000003 306.333333,20.4477156 306.333333,21.0000003 L306.333333,27.3333337 C306.333333,27.8856184 305.885618,28.3333337 305.333333,28.3333337 L304.333333,28.3333337 C303.781049,28.3333337 303.333333,27.8856184 303.333333,27.3333337 L303.333333,21.0000003 C303.333333,20.4477156 303.781049,20.0000003 304.333333,20.0000003 Z M309,17.666667 L310,17.666667 C310.552285,17.666667 311,18.1143823 311,18.666667 L311,27.3333337 C311,27.8856184 310.552285,28.3333337 310,28.3333337 L309,28.3333337 C308.447715,28.3333337 308,27.8856184 308,27.3333337 L308,18.666667 C308,18.1143823 308.447715,17.666667 309,17.666667 L309,17.666667 Z" id="Cellular-Connection" x-bind:fill="state.ui_status_bar_style" fill-rule="nonzero"></path>
                                <g id="Bars-/-Status-Bar-/-iPhone-/-x-/-Time---On-Dark" transform="translate(21.000000, 9.000000)" x-bind:fill="state.ui_status_bar_style" font-size="15" font-weight="500" letter-spacing="-0.3">
                                    <text id="Time">
                                        <tspan x="11.4867188" y="19.3299999">9:41</tspan>
                                    </text>
                                </g>

                                <!-- Eyedropper ui_status_bar_style -->
                                <g class="eyedropper" x-on:click="@this.set('selectedElement', 'ui_status_bar_style')" wire:click="switchStatusBarStyle" transform="translate(10, 15)" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <circle id="Oval" fill="#445CD3" cx="10" cy="10" r="10"></circle>
                                    <path d="M13.948,7.552 C13.607,7.852 13.5715,8.371 13.8715,8.714 L13.252,9.2555 L10.544,6.1565 L11.1635,5.6155 C11.463,5.9575 11.9825,5.992 12.3235,5.6925 L13.9465,4.3045 C14.1815,4.101 14.4705,4 14.759,4 C15.449,4 16,4.564 16,5.2355 C16,5.6005 15.839,5.932 15.573,6.1645 L13.948,7.552 Z M10.4765,9.5 L9.2265,9.5 L11.0075,7.9385 L10.465,7.3185 L6.7955,10.512 C6.201,11.0285 6.6115,11.4275 6.1215,12.1095 C6.055,12.2025 6.0165,12.2955 6.006,12.3845 C5.973,12.6545 6.1685,12.882 6.4145,12.9125 C6.5115,12.924 6.6195,12.905 6.7225,12.844 C7.4975,12.388 7.8115,12.9025 8.4205,12.3705 L12.0905,9.1785 L11.55,8.558 L10.4765,9.5 Z M5.9235,13.25 C5.656,14.2765 5,14.4175 5,15.092 C5,15.5935 5.417,16 5.9235,16 C6.43,16 6.8405,15.5935 6.8405,15.092 C6.8405,14.4175 6.191,14.2765 5.9235,13.25 Z" id="Shape" fill="#FFFFFF" fill-rule="nonzero"></path>
                                </g>
                                <!-- /Eyedropper ui_status_bar_style -->
                            </g>
                        </g>
                    </g>
                    <path d="M103.598361,16 L302.598361,16 L302.598361,16 C302.598361,32.5685425 289.166903,46 272.598361,46 L133.598361,46 C117.029818,46 103.598361,32.5685425 103.598361,16 L103.598361,16 Z" id="bar" fill="#070707"></path>
                </g>
            </g>
        </g>
    </svg>
    <!-- /iPhone SVG  -->

    <!-- Color picker modal -->
    <div x-show="show" class="fixed inset-0" x-on:click="show = false; selectedElement = null">
        <div class="absolute inset-0"></div>
    </div>

    <div x-show="show" class="absolute top-10 right-auto transform-none my-auto bg-secondary rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:top-1/2 sm:right-10 sm:transform sm:-translate-y-1/2 sm:max-w-sm">
        <div class="px-6 pt-4 pb-4">
            <div class="text-lg text-center">
                {{ __('Select color') }}
            </div>

            <div class="mt-4">
                <div id="colorPicker" class="flex justify-center" wire:ignore></div>
            </div>

            <template x-if="selectedElement !== null">
                <div class="mt-4">
                    <x-label x-bind:for="selectedElement" :for="$selectedElement" value="Hex" />
                    <x-input id="{{ $selectedElement }}" type="text" class="mt-1 block w-full" wire:model.live="{{ 'state.' . $selectedElement }}" />
                    <x-input-error x-bind:for="selectedElement" :for="$selectedElement" class="mt-2" />
                </div>
            </template>
        </div>
    </div>
    <!-- /Color picker modal -->

    <script type="module">
        (function () {
            // Warning before exiting page
            window.onbeforeunload = function (e) {
                let event = e || window.event;
                let message = @json(__('Are you sure you want to leave? Your creation will not be saved.'));

                // For IE and Firefox
                if (event) {
                    event.returnValue = message;
                }

                // For Safari
                return message;
            };
        })();
    </script>
</div>
