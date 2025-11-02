<template>
   <div class="modal-content fm-modal-preview">
      <div class="text-center modal-body">
         <template v-if="showCropperModule">
            <cropper-module
               v-bind:imgSrc="imgSrc"
               v-bind:maxHeight="maxHeight"
               v-on:closeCropper="closeCropper"
            ></cropper-module>
         </template>
         <transition v-else name="fade" mode="out-in">
            <i
               v-if="!imgSrc"
               class="p-5 fas fa-spinner fa-spin fa-5x text-muted"
            ></i>
            <img
               v-else
               v-bind:src="imgSrc"
               v-bind:alt="selectedItem.basename"
               v-bind:style="{ 'max-height': maxHeight + 'px' }"
            />
         </transition>
      </div>
      <div v-if="showFooter" class="d-flex justify-content-between">
         <span class="d-block">
            <button class="btn btn-info" v-on:click="showCropperModule = true">
               <i class="fas fa-crop-simple"></i>
            </button>
         </span>
      </div>
   </div>
</template>

<script>
// import CropperModule from 'additions/Cropper.vue';
// import translate from './../../../mixins/translate';
import helper from "~/components/filemanager/mixins/filemanagerHelper";
// import GET from './../../../http/get';

export default {
   name: "Preview",
   mixins: [helper],
   data() {
      return {
         showCropperModule: false,
         imgSrc: "",
      };
   },
   mounted() {
      if (window.IntersectionObserver) {
         const observer = new IntersectionObserver(
            (entries, obs) => {
               entries.forEach((entry) => {
                  if (entry.isIntersecting) {
                     this.loadImage();
                     obs.unobserve(this.$el);
                  }
               });
            },
            {
               root: null,
               threshold: "0.5",
            }
         );
         // add observer for template
         observer.observe(this.$el);
      } else {
         this.loadImage();
      }
   },
   computed: {
      // auth() {
      //    return this.$store.getters['fm/settings/authHeader'];
      // },

      selectedDisk() {
         return this.$store.getters["fm/content/selectedDisk"];
      },

      selectedItem() {
         return this.$store.getters["fm/content/selectedList"][0];
      },

      showFooter() {
         return (
            this.canCrop(this.selectedItem.extension) && !this.showCropperModule
         );
      },

      maxHeight() {
         if (this.$store.state.fm.modal.modalBlockHeight) {
            return this.$store.state.fm.modal.modalBlockHeight - 170;
         }

         return 300;
      },
   },
   methods: {
      /**
       * Can we crop this image?
       * @param extension
       * @returns {boolean}
       */
      canCrop(extension) {
         return this.$store.state.fm.settings.cropExtensions.includes(
            extension.toLowerCase()
         );
      },

      /**
       * Close cropper
       */
      closeCropper() {
         this.showCropperModule = false;
         this.loadImage();
      },

      /**
       * Load image
       */
      async loadImage() {
         await this.$axios
            .get(
               `media-manger/file-manager/preview?disk=${this.selectedDisk}&path=${this.selectedItem.path}`
            )
            .then((response) => {
               const mimeType = response.headers["content-type"].toLowerCase();
               const imgBase64 = Buffer.from(response.data, "binary").toString(
                  "base64"
               );

               this.imgSrc = `data:${mimeType};base64,${response.data}`;
            });
      },
   },
};
</script>

<style lang="scss">
.fm-modal-preview {
   .modal-body {
      padding: 0;

      img {
         max-width: 100%;
      }
   }

   & > .d-flex {
      padding: 1rem;
      border-top: 1px solid #e9ecef;
   }
}
</style>
