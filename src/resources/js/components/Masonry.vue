<template>
    <div class="-mx-4">
        <div id="file-preview-modal" class="inset-0 flex flex-col justify-center z-30"
             :class="{ 'hidden': !modalVisible, 'fixed': modalVisible }">
            <div class="absolute top-0 right-0">
                <a class="block cursor-pointer p-4 m-4" @click="closeModal()">
                    <i class="fas fa-times fa-2x" aria-hidden="true"></i>
                </a>
            </div>

            <div class="w-full h-full p-4 lg:p-16" @click="closeModal()">
                <template v-if="currentFile && currentFile['mimetype'] && currentFile['mimetype'].startsWith('image/')">
                    <div class="w-full h-full bg-contain bg-no-repeat bg-center"
                         style="background-origin: content-box;"
                         :style="{ 'background-image': 'url(/preview/' + currentFile['id'] + ')' }">
                    </div>
                </template>
                <template
                    v-else-if="currentFile && currentFile['mimetype'] && currentFile['mimetype'].startsWith('video/')">
                    <video id="video-player" v-if="" class="w-auto h-full mx-auto" controls autoplay preload="metadata">
                        <source :src="'/preview/' + currentFile['id'] + '.webm'" type="video/webm"/>
                        <source :src="'/preview/' + currentFile['id'] + '.mp4'" type="video/mp4"/>
                        <source :src="'/preview/' + currentFile['id']" :type="currentFile['mimetype']"/>
                    </video>
                </template>
                <template v-else>
                    <p class="p-4 bg-red-200 text-red-900 font-bold">Unbekanntes Dateiformat!</p>
                </template>
            </div>
        </div>

        <div class="masonry block w-full">
            <div class="grid-sizer"></div>
            <div class="grid-item" v-for="file in files" :key="file.id"
                 :class="{ 'grid-item--width2': file.ratio && (file.ratio > 1.5 ) }">
                <a class="cursor-pointer" @click="openFile(file)">
                    <img class="w-full h-auto lazy preview-image"
                         :src="'/preview/' + file['id'] + (file['mimetype'].startsWith('video/') ? '-thumbnail' : '')"
                         :alt="file.name">
                </a>
            </div>
        </div>

        <div v-if="loading !== false" class="flex flex-col justify-center text-center mb-16">
            {{ loading }}
        </div>
    </div>
</template>

<script>
import Masonry from 'masonry-layout'
import imagesLoaded from 'imagesloaded'
import axios from 'axios'
import _ from 'lodash'

export default {
    data () {
        return {
            files: [],
            masonry: null,
            modalVisible: false,
            currentFile: null,
            loading: 'Lade Vorschaubilder...',
            page: 1
        }
    },
    mounted () {
        console.debug('Mounted masonry.')

        this.masonry = new Masonry(this.$el, {
            initLayout: false,
            itemSelector: '.grid-item',
            columnWidth: '.grid-sizer'
        })

        this.loadImages()

        window.addEventListener('scroll', _.throttle(this.testImageUpdate, 500, {
            trailing: true
        }))
        window.addEventListener('resize', _.throttle(this.testImageUpdate, 500, {
            trailing: true
        }))
        window.addEventListener('ondeviceorientation', _.throttle(this.testImageUpdate, 500, {
            trailing: true
        }))
    },
    methods: {
        openFile (file) {
            console.debug('Open preview modal', file.id)

            this.modalVisible = true
            this.currentFile = file
        },

        closeModal () {
            console.debug('Close preview modal')

            this.modalVisible = false
            this.currentFile = null
        },

        testImageUpdate () {
            const loadingOffset = 2000
            const scrollY = window.scrollY + window.innerHeight
            const bodyHeight = document.body.clientHeight

            if (scrollY > (bodyHeight - loadingOffset)) {
                this.loadImages()
            }
        },

        updateLayout () {
            console.debug('Update layout')

            const that = this

            imagesLoaded(this.$el, function () {
                console.log('All images loaded')

                that.loading = false

                that.masonry.reloadItems()
                that.masonry.layout()
            })
        },

        loadImages () {
            const that = this

            this.loading = 'Lade Vorschaubilder...'

            axios.get('/list/' + this.page)
                .then(function (response) {
                    if (response.data && response.data.length > 0) {
                        console.log('Added more images')

                        that.page += 1
                        that.files.push(...response.data)

                        requestAnimationFrame(function () {
                            that.updateLayout()
                        })
                    } else {
                        this.loading = 'Das waren die letzten Bilder :('
                    }
                })
                .catch(function (err) {
                    console.error(err)

                    this.loading = 'Beim laden der Bilder ist ein Fehler aufgetreten!'
                })
        }
    }
}
</script>

<style scoped>
#file-preview-modal {
    background-color: rgba(255, 255, 255, .8);
}

.grid-sizer {
    @apply w-full;
}

.grid-item {
    @apply block float-left w-full px-4 mb-8 overflow-hidden;

    transition: opacity .2s ease;
}

.grid-item--width2 {
    @apply w-full;
}

@media (min-width: 640px) {
    .grid-sizer,
    .grid-item {
        @apply w-1/2;
    }

    .grid-item--width2 {
        @apply w-full;
    }
}

@media (min-width: 768px) {
    .grid-sizer,
    .grid-item {
        @apply w-1/3;
    }

    .grid-item--width2 {
        @apply w-2/3;
    }
}

@media (min-width: 1024px) {
    .grid-sizer,
    .grid-item {
        @apply w-1/4;
    }

    .grid-item--width2 {
        @apply w-1/2;
    }
}
</style>
