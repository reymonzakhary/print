<template>
  <div
    v-if="
      producer.handshake === 'accepted' ||
      producer.handshake === 'pending' ||
      producer.handshake === 'suspended' ||
      producer.handshake === 'rejected' ||
      producer.contract?.st === 320 ||
      producer.contract?.st === 301 ||
      producer.contract?.st === 327 ||
      producer.contract?.st === 319
    "
    class="absolute text-center"
    :class="{
      'bottom-0 left-0 h-full w-6 border-l-2': position === 'left',
      'left-0 top-0 h-7 w-full border-t-2': position === 'top',
      'border-green-400 bg-green-100':
        producer.handshake === 'accepted' || producer.contract?.st === 320,
      'border-blue-400 bg-blue-100':
        producer.handshake === 'pending' || producer.contract?.st === 301,
      'border-amber-400 bg-amber-100':
        producer.handshake === 'suspended' || producer.contract?.st === 327,
      'border-red-400 bg-red-100':
        producer.handshake === 'rejected' || producer.contract?.st === 319,
    }"
  >
    <div class="relative flex h-full w-full items-center justify-center">
      <div
        class="absolute text-xs"
        :class="{
          '-rotate-90 transform': position === 'left',
          'top-1 text-center': position === 'top',
          'text-green-500': producer.handshake === 'accepted' || producer.contract?.st === 320,
          'text-blue-500': producer.handshake === 'pending' || producer.contract?.st === 301,
          'text-amber-500': producer.handshake === 'suspended' || producer.contract?.st === 327,
          'text-red-500': producer.handshake === 'rejected' || producer.contract?.st === 319,
        }"
      >
        {{
          producer.handshake === "accepted" || producer.contract?.st === 320
            ? $t("connected")
            : producer.handshake === "pending" || producer.contract?.st === 301
              ? $t("pending")
              : producer.handshake === "suspended" || producer.contract?.st === 327
                ? $t("suspended")
                : $t("rejected")
        }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "HandshakeStatus",
  props: {
    producer: {
      type: Object,
      required: true,
    },
    position: {
      type: String,
      validator: (value) => {
        return ["top", "left"].includes(value);
      },
      default: "left",
    },
  },
};
</script>
