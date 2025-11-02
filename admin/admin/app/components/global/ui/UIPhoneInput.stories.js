import UIPhoneInput from "./UIPhoneInput.vue";

export default {
  title: "Global/UI/PhoneInput",
  component: UIPhoneInput,
  argTypes: {
    modelValue: {
      control: {
        type: "object",
      },
    },
    placeholder: {
      control: {
        type: "text",
      },
    },
    disabled: {
      control: {
        type: "boolean",
      },
    },
    scale: {
      control: {
        type: "select",
        options: ["sm", "md"],
      },
    },
    icon: {
      control: {
        type: "array",
      },
    },
  },
};

const Story = (args, { argTypes }) => ({
  props: Object.keys(argTypes),
  components: { UIPhoneInput },
  data() {
    return {
      phoneValue: args.modelValue || { dialCode: "+1", phoneNumber: "" },
    };
  },
  template: `
    <div style="width: 400px">
        <UIPhoneInput 
            v-bind="$props" 
            v-model="phoneValue"
            v-on="$listeners" 
        />
        <div style="margin-top: 16px; font-size: 12px; color: #666;">
            <strong>Value:</strong> {{ JSON.stringify(phoneValue, null, 2) }}
        </div>
    </div>
    `,
});

export const Default = Story.bind({});
Default.args = {
  name: "phone",
  placeholder: "Enter phone number...",
};

export const Disabled = Story.bind({});
Disabled.args = {
  name: "phone-disabled",
  disabled: true,
  placeholder: "Enter phone number...",
};

export const WithIcon = Story.bind({});
WithIcon.args = {
  name: "phone-icon",
  icon: ["fas", "phone"],
  placeholder: "Enter phone number...",
};

export const WithPrefilledValue = Story.bind({});
WithPrefilledValue.args = {
  name: "phone-prefilled",
  modelValue: { dialCode: "+44", phoneNumber: "1234567890" },
  placeholder: "Enter phone number...",
};

export const MediumScale = Story.bind({});
MediumScale.args = {
  name: "phone-medium",
  scale: "md",
  placeholder: "Enter phone number...",
};

export const CustomCountries = Story.bind({});
CustomCountries.args = {
  name: "phone-custom",
  placeholder: "Enter phone number...",
  countries: [
    { code: "US", flag: "🇺🇸", dialCode: "+1", name: "United States" },
    { code: "EG", flag: "🇪🇬", dialCode: "+20", name: "Egypt" },
    { code: "NL", flag: "🇳🇱", dialCode: "+31", name: "Netherlands" },
    { code: "DE", flag: "🇩🇪", dialCode: "+49", name: "Germany" },
    { code: "BE", flag: "🇧🇪", dialCode: "+32", name: "Belgium" },
    { code: "GB", flag: "🇬🇧", dialCode: "+44", name: "United Kingdom" },
    { code: "FR", flag: "🇫🇷", dialCode: "+33", name: "France" },
    { code: "IT", flag: "🇮🇹", dialCode: "+39", name: "Italy" },
    { code: "ES", flag: "🇪🇸", dialCode: "+34", name: "Spain" },
    { code: "PT", flag: "🇵🇹", dialCode: "+351", name: "Portugal" },
    { code: "AT", flag: "🇦🇹", dialCode: "+43", name: "Austria" },
    { code: "CH", flag: "🇨🇭", dialCode: "+41", name: "Switzerland" },
    { code: "SE", flag: "🇸🇪", dialCode: "+46", name: "Sweden" },
    { code: "NO", flag: "🇳🇴", dialCode: "+47", name: "Norway" },
    { code: "DK", flag: "🇩🇰", dialCode: "+45", name: "Denmark" },
    { code: "FI", flag: "🇫🇮", dialCode: "+358", name: "Finland" },
    { code: "PL", flag: "🇵🇱", dialCode: "+48", name: "Poland" },
    { code: "CZ", flag: "🇨🇿", dialCode: "+420", name: "Czech Republic" },
    { code: "HU", flag: "🇭🇺", dialCode: "+36", name: "Hungary" },
    { code: "RO", flag: "🇷🇴", dialCode: "+40", name: "Romania" },
    { code: "BG", flag: "🇧🇬", dialCode: "+359", name: "Bulgaria" },
    { code: "GR", flag: "🇬🇷", dialCode: "+30", name: "Greece" },
    { code: "HR", flag: "🇭🇷", dialCode: "+385", name: "Croatia" },
    { code: "SI", flag: "🇸🇮", dialCode: "+386", name: "Slovenia" },
    { code: "SK", flag: "🇸🇰", dialCode: "+421", name: "Slovakia" },
    { code: "EE", flag: "🇪🇪", dialCode: "+372", name: "Estonia" },
    { code: "LV", flag: "🇱🇻", dialCode: "+371", name: "Latvia" },
    { code: "LT", flag: "🇱🇹", dialCode: "+370", name: "Lithuania" },
    { code: "LU", flag: "🇱🇺", dialCode: "+352", name: "Luxembourg" },
    { code: "MT", flag: "🇲🇹", dialCode: "+356", name: "Malta" },
    { code: "CY", flag: "🇨🇾", dialCode: "+357", name: "Cyprus" },
    { code: "IE", flag: "🇮🇪", dialCode: "+353", name: "Ireland" },
  ]
};

export const WithValidation = Story.bind({});
WithValidation.args = {
  name: "phone-validation",
  placeholder: "Enter phone number...",
  rules: "required|min:10",
};
