// stores/eventStore.js
import { defineStore } from "pinia";

export const useEventStore = defineStore("event", {
  state: () => ({
    events: new Map(),
  }),
  actions: {
    on(event, handler) {
      if (!this.events.has(event)) {
        this.events.set(event, []);
      }
      this.events.get(event).push(handler);
    },
    off(event, handler) {
      if (this.events.has(event)) {
        const handlers = this.events.get(event);
        const index = handlers.indexOf(handler);
        if (index > -1) {
          handlers.splice(index, 1);
        }
        if (handlers.length === 0) {
          this.events.delete(event);
        }
      }
    },
    emit(event, payload) {
      if (this.events.has(event)) {
        for (const handler of this.events.get(event)) {
          handler(payload);
        }
      }
    },
  },
});
