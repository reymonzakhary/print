import UICardHeader from './UICardHeader.vue'

export default {
    title: 'Global/UI/Card/Header',
    component: UICardHeader,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UICardHeader },
    template: `
        <UICardHeader v-bind="$props" v-on="$listeners">
            <template v-if="${'left' in args}" #left>${args.left}</template>
            <template v-if="${'center' in args}" #center>${args.center}</template>
            <template v-if="${'right' in args}" #right>${args.right}</template>
        </UICardHeader>
    `,
});

export const Empty = Story.bind({});
Empty.args = {};

export const withLeft = Story.bind({});
withLeft.args = {
    left: `<UICardHeaderTitle title="Title" :icon="['fal', 'users']" />`,
};

export const withCenter = Story.bind({});
withCenter.args = {
    center: '<UserAvatar size="80" class="relative z-12 mx-auto" style="top: 19px" />',
};

export const withRight = Story.bind({});
withRight.args = {
    right: `<UICardHeaderButton :icon="['fal', 'users']">Add Users</UICardHeaderButton>`,
};

export const withTabs = Story.bind({});
withTabs.args = {
    left: `\n        <div class=\"flex\">\n                        <UICardHeaderTab label=\"English\" @click=\"$emit('onTabSwitch', 0)\" active />\n            <UICardHeaderTab label=\"Dutch\" @click=\"$emit('onTabSwitch', 1)\" />\n            <UICardHeaderTab label=\"French\" @click=\"$emit('onTabSwitch', 2)\" />\n\n        </div>\n    `,
    center: `\n        <div class=\"flex\">\n                        <UICardHeaderTab label=\"English\" @click=\"$emit('onTabSwitch', 0)\" active />\n            <UICardHeaderTab label=\"Dutch\" @click=\"$emit('onTabSwitch', 1)\" />\n            <UICardHeaderTab label=\"French\" @click=\"$emit('onTabSwitch', 2)\" />\n\n        </div>\n    `,
    right: `\n        <div class=\"flex\">\n                        <UICardHeaderTab label=\"English\" @click=\"$emit('onTabSwitch', 0)\" active />\n            <UICardHeaderTab label=\"Dutch\" @click=\"$emit('onTabSwitch', 1)\" />\n            <UICardHeaderTab label=\"French\" @click=\"$emit('onTabSwitch', 2)\" />\n\n        </div>\n    `,
}