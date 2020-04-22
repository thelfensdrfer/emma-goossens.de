<template>
    <div class="masonry -mx-4">
        <div v-if="loading" class="flex flex-col justify-center text-center mb-16">
            Lade Vorschaubilder...
        </div>

        <div id="file-preview-modal" class="inset-0 flex flex-col justify-center z-30" :class="{ 'hidden': !modalVisible, 'fixed': modalVisible }">
            <div class="absolute top-0 right-0">
                <a class="block cursor-pointer p-4 m-4" @click="closeModal()">
                    <i class="fas fa-times fa-2x" aria-hidden="true"></i>
                </a>
            </div>

            <div class="w-full h-full p-4 lg:p-16" @click="closeModal()">
                <template v-if="currentFile && currentFile['mimetype'] && currentFile['mimetype'].startsWith('image/')">
                    <div class="w-full h-full bg-contain bg-no-repeat bg-center" style="background-origin: content-box;" :style="{ 'background-image': 'url(/preview/' + currentFile['id'] + ')' }">
                    </div>
                </template>
                <template v-else-if="currentFile && currentFile['mimetype'] && currentFile['mimetype'].startsWith('video/')">
                    <video id="video-player" v-if="" class="w-auto h-full mx-auto" controls autoplay preload="auto">
                        <source :src="'/preview/' + currentFile['id'] + '.webm'" type="video/webm" />
                        <source :src="'/preview/' + currentFile['id'] + '.mp4'" type="video/mp4" />
                        <source :src="'/preview/' + currentFile['id']" :type="currentFile['mimetype']" />
                    </video>
                </template>
                <template v-else>
                    <p class="p-4 bg-red-200 text-red-900 font-bold">Unbekanntes Dateiformat!</p>
                </template>
            </div>
        </div>

        <div class="grid-sizer"></div>
        <div class="grid-item" v-for="file in files" :key="file.id" :class="{ 'grid-item--width2': file.ratio && (file.ratio > 1.5 ), 'opacity-0': loading }">
            <a class="cursor-pointer" @click="openFile(file)">
                <img class="w-full h-auto" :src="'/preview/' + file['id'] + (file['mimetype'].startsWith('video/') ? '-thumbnail' : '')" :alt="file.name">
            </a>
        </div>
    </div>
</template>

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

<script>
    import Masonry from 'masonry-layout'
    import imagesLoaded from 'imagesloaded'

    export default {
        props: {
            files: {
                required: true,
                type: Array,
            }
        },
        data () {
            return {
                loading: true,
                masonry: null,
                modalVisible: false,
                currentFile: null
            }
        },
        mounted () {
            console.debug('Mounted masonry.')

            let that = this;

            imagesLoaded(this.$el, function () {
                that.masonry = new Masonry(that.$el, {
                    itemSelector: '.grid-item',
                    columnWidth: '.grid-sizer'
                });

                that.loading = false;
            });
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
            }
        }
    }
</script>
