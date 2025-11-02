function useTemplateRepository() {
   // Always get the $api from the useNuxtApp() hook (this is the only way to get provided global values in a composable)
   const { $api } = useNuxtApp();

   // You can either pass the whole query to $fetch
   async function index(query) {
      // Do not await $fetch here, it will cause issues!
      return $api("endpoint", {
         query,
      });
   }

   async function create(payload) {
      return $api("endpoint", {
         method: "POST",
         body: payload,
      });
   }

   // Or you can pass specific paramaters, narrowing down the query.
   async function read(id) {
      return $api("endpoint", {
         query: id,
      });
   }

   async function update(payload) {
      return $api("endpoint", {
         method: "PUT",
         body: payload,
      });
   }

   async function destroy(id) {
      return $api("endpoint", {
         method: "DELETE",
         query: id,
      });
   }

   return {
      index,
      create,
      read,
      update,
      destroy,
   };
}
