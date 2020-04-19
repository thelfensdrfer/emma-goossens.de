<template>
    <div class="masonry -mx-4">
        <div v-if="loading" class="flex flex-col justify-center text-center mb-16">
            Lade Vorschaubilder...
        </div>

        <div class="grid-sizer"></div>
        <div class="grid-item" v-for="file in files" :key="file.id" :class="{ 'grid-item--width2': file.ratio && (file.ratio > 1.5 )}" :data-ratio="file.ratio" :data-thumbnail-width="file.width" :data-thumbnail-height="file.height">
            <a :href="file.downloadLink">
                <img class="w-full h-auto" :src="file.thumbnailLink" :alt="file.name">
            </a>
        </div>
    </div>
</template>

<style scoped>
    .grid-sizer {
        @apply w-full;
    }

    .grid-item {
        @apply block float-left w-full px-4 mb-8 overflow-hidden;
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
    import Masonry from 'masonry-layout';
    import imagesLoaded from 'imagesloaded';

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
            }
        },
        mounted () {
            console.debug('Masonry mounted.')

            let that = this;

            imagesLoaded(this.$el, function () {
                that.masonry = new Masonry(that.$el, {
                    itemSelector: '.grid-item',
                    columnWidth: '.grid-sizer'
                });

                that.loading = false;
            });
        }
    }
</script>
