import SlideInModal from './SlideInModal.vue';

export default {
    title: 'Global/Modal/SlideInModal',
    component: SlideInModal,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { SlideInModal },
    template: `
    <div id="__layout">
        <div>
            <SlideInModal v-bind="$props" v-on="$listeners" />
        </div>
    </div>
    `,
});

export const Default = Story.bind({});
Default.args = {
    // props
};