<template>
  <div class="my-4">
    <UICard class="mt-auto mb-4 text-yellow-600 bg-yellow-100 border border-yellow-500 rounded">
      <div class="flex p-4">
        <div>
          <h1 class="mb-2 text-2xl">
            <font-awesome-icon :icon="['fas', 'lock']" class="mr-1" />
            {{ $t("This resource is currently") }}
            <span class="font-bold">
              {{ $t("locked") }}
            </span>
            {{ $t("for you.") }}
          </h1>
          <p>
            {{ $t("This means that someone else is currently editing this resource.") }}
            <br />
            {{ $t("You can still view the resource, but you cannot edit it.") }}
          </p>
        </div>
      </div>
    </UICard>
  </div>
</template>

<script>
export default {
  name: "ResourceLockedNotification",
  props: {
    locked_by: {
      type: Object,
      required: true,
      default: () => {},
    },
  },
};
</script>
