import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import '@fortawesome/fontawesome-free/css/all.css';
import { createPinia } from 'pinia';


import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import FloatLabel from 'primevue/floatlabel';
import Checkbox from 'primevue/checkbox';
import InputText from 'primevue/inputtext';
import Menu from 'primevue/menu';
import Button from 'primevue/button';
import TieredMenu from 'primevue/tieredmenu';
import ToggleSwitch from 'primevue/toggleswitch';
import Breadcrumb from 'primevue/breadcrumb';
import SelectButton from 'primevue/selectbutton';
import Tooltip from 'primevue/tooltip';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import ConfirmPopup from 'primevue/confirmpopup';
import ConfirmationService from 'primevue/confirmationservice';
import Toast from 'primevue/toast';
import ToastService from 'primevue/toastservice';
import ConfirmDialog from 'primevue/confirmdialog';

import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import Card from 'primevue/card';

import Stepper from 'primevue/stepper';
import StepList from 'primevue/steplist';
import StepPanels from 'primevue/steppanels';
import StepItem from 'primevue/stepitem';
import Step from 'primevue/step';
import StepPanel from 'primevue/steppanel';

const appName = import.meta.env.VITE_APP_NAME || 'Precious';

createInertiaApp({
    title: () => `${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        const pinia = createPinia();
        // Registrace komponent
        app.component('PrimaryButton', PrimaryButton);
        app.component('SecondaryButton', SecondaryButton);
        app.use(PrimeVue, {
            theme: {
                preset: Aura
            }
        });
        app.use(ConfirmationService);   
        app.use(pinia);
        app.use(ToastService);
        app.component('Checkbox', Checkbox);
        app.component('FloatLabel', FloatLabel);
        app.component('InputText', InputText);
        app.component('Menu', Menu);
        app.component('Button', Button);
        app.component('TieredMenu', TieredMenu);
        app.component('ToggleSwitch', ToggleSwitch);
        app.component('Breadcrumb', Breadcrumb);
        app.component('SelectButton', SelectButton);
        app.component('Dialog', Dialog);
        app.component('InputNumber', InputNumber);
        app.component('InputNumber', InputNumber);
        app.component('ConfirmPopup', ConfirmPopup);
        app.component('ConfirmDialog', ConfirmDialog);
        app.component('Toast', Toast);
        app.component('Tabs', Tabs);
        app.component('TabList', TabList)
        app.component('Tab', Tab);
        app.component('TabPanel', TabPanel);
        app.component('TabPanels', TabPanels);
        app.component('Card', Card);

        app.component('Stepper', Stepper);
        app.component('StepList', StepList);
        app.component('StepPanels', StepPanels);
        app.component('StepItem', StepItem);
        app.component('Step', Step);
        app.component('StepPanel', StepPanel);

        app.directive('tooltip', Tooltip);
        app.use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

