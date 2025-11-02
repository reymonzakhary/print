import UIInputText from './UIInputText.vue';

export default {
    title: 'Global/UI/InputText',
    component: UIInputText,
    argTypes: {
        "value": {
            control: {
                type: "text"
            }
        },
        "placeholder": {
            control: {
                type: "text"
            }
        },
        "invalid": {
            control: {
                type: "boolean"
            }
        },
        "type": {
            control: {
                type: "select",
                options: ["text", "password", "email", "number", "tel", "url"]
            }
        },
        "icon": {
            control: {
                type: "array"
            }
        },
    }
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UIInputText },
    template: `
    <div style="width: 300px">
        <UIInputText v-bind="$props" v-on="$listeners" />
    </div>
    `,
});

export const Default = Story.bind({});

export const Disabled = Story.bind({});
Disabled.args = {
    disabled: true
};

export const WithIcon = Story.bind({});
WithIcon.args = {
    icon: ["fas", "user"]
};

export const Invalid = Story.bind({});
Invalid.args = {
    invalid: true
};

export const InvalidWithIcon = Story.bind({});
InvalidWithIcon.args = {
    invalid: true,
    icon: ["fas", "user"]
};

export const Password = Story.bind({});
Password.args = {
    value: "password",
    type: "password"
};

export const WithPrefix = Story.bind({});
WithPrefix.args = {
    prefix: "prefix"
};

export const WithPrefixAndIcon = Story.bind({});
WithPrefixAndIcon.args = {
    prefix: "@",
    placeholder: "Username",
    icon: ["fas", "user"]
};

export const WithPrefixContaingIcon = Story.bind({});
WithPrefixContaingIcon.args = {
    prefix: ["fas", "user"]
};