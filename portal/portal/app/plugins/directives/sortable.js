import Sortable from 'sortablejs/modular/sortable.core.esm.js';
const SortableDirective = {
    inserted( el, binding, vnode ) {
        let options = binding.value;
        options.onEnd = (e) => vnode.data.on.sorted( e );
        const sortable = Sortable.create( el, binding.value );
    }
};
export default SortableDirective;

// https://medium.com/js-dojo/drag-and-drop-blocks-with-vue-js-f5585a80ec37 for example