<template>
    <fragment>
        <div class="flex flex-grow items-center w-full overflow-hidden lg:hidden" style="min-height: 48px;">
            <div class="flex-1">
                <button @click="isOpen = !isOpen" class="px-3 py-2 border rounded text-orange-500 border-orange-500 hover:text-orange-400 hover:border-orange-400">
                    <svg class="fill-current h-3 w-3" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <title>Menu</title>
                        <path v-if="isOpen" d="M16.672988,12.1848426 C17.0788943,12.6051083 17.0730892,13.2731418 16.6599408,13.6862902 C16.2467924,14.0994386 15.5787589,14.1052437 15.1584932,13.6993374 L9.98628977,8.52820508 L4.81515744,13.6993374 C4.39489167,14.1052437 3.72685818,14.0994386 3.31370977,13.6862902 C2.90056136,13.2731418 2.89475633,12.6051083 3.30066259,12.1848426 L8.47179492,7.01263916 L3.30066259,1.84150684 C2.89475633,1.42124106 2.90056136,0.753207573 3.31370977,0.340059165 C3.72685818,-0.0730892433 4.39489167,-0.0788942738 4.81515744,0.32701199 L9.98736084,5.49814432 L15.1584932,0.32701199 C15.4274118,0.0485799487 15.8256398,-0.0630856764 16.2001179,0.0349338873 C16.5745959,0.132953451 16.8670465,0.425404077 16.9650661,0.799882133 C17.0630857,1.17436019 16.9514201,1.57258817 16.672988,1.84150684 L11.5018557,7.01371023 L16.672988,12.1848426 Z"></path>
                        <path v-if="!isOpen" d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z"/>
                    </svg>
                </button>
            </div>
            <div class="gn-kurozora">
                <a class="gn-link gn-link-kurozora block m-0" href="/"></a>
            </div>
        </div>

        <div :class="isOpen ? 'block' : 'hidden'" class="gn-header">
            <slot></slot>
        </div>
    </fragment>
</template>

<script>
    export default {
        data() {
            return {
                isOpen: false
            }
        }
    }
</script>

<style scoped>

</style>
