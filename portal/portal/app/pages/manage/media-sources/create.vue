<template>
  <div class="px-4">
    <transition name="fade">
      <component :is="MSComponent" />
    </transition>
  </div>
</template>

<script>
import NewMediaSourceName from "~/components/mediasources/NewMediaSourceName";
import NewMediaSourceFilesAndFolders from "~/components/mediasources/NewMediaSourceFilesAndFolders";
import { mapState } from "vuex";

export default {
  components: {
    NewMediaSourceName,
    NewMediaSourceFilesAndFolders,
  },
  computed: {
    ...mapState({
      MSComponent: (state) => state.mediasource.MSComponent,
    }),
  },
};
</script>
