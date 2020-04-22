<template>
    <div class="fixed top-0 left-0 m-4 mt-8 z-20">
        <a class="cursor-pointer p-4" @click="toggle">
            <i class="fas fa-moon-stars fa-2x" aria-hidden="true"></i>
        </a>
    </div>
</template>

<script>
    import particlesConfig from './../particles.js'

    export default {
        name: 'NightMode',
        mounted () {
            console.debug('Mounted night mode.')
        },
        methods: {
            toggle () {
                let body = document.querySelector('body')

                if (body.classList.contains('night')) {
                    // Disable night mode
                    document.querySelector('#particles-js').remove()

                    document.cookie = 'night=0; expires=Thu, Jan 31 2030 00:00:00 UTC; path=/'
                } else {
                    // Enable night mode
                    let particles = document.createElement('div')
                    particles.setAttribute('id', 'particles-js')

                    body.prepend(particles)

                    particlesJS('particles-js', particlesConfig)

                    document.cookie = 'night=1; expires=Thu, Jan 31 2030 00:00:00 UTC; path=/'
                }

                body.classList.toggle('night')
            }
        }
    }
</script>

<style scoped>

</style>
