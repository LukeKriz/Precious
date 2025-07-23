import { defineStore } from 'pinia';
import { ref } from 'vue';

import { useToast } from "primevue/usetoast";

export const useGlobalStore = defineStore('global', () => {
    const globalSuccess = ref(null);
    const globalError = ref(null);

    const setGlobalSuccess = (message) => {
        globalSuccess.value = message;
        setTimeout(() => {
            globalSuccess.value = null;
        }, 3000);
    };

    const setGlobalError = (message) => {
        globalError.value = message;
        setTimeout(() => {
            globalError.value = null;
        }, 3000);
    };

    return { globalSuccess, setGlobalSuccess, globalError, setGlobalError };
});




export const useToastStore = defineStore("toast", () => {
    const toast = useToast();
  
    const showToast = (severity, summary, detail) => {
      toast.add({
        severity,
        summary,
        detail,
        life: 3000,
        group: 'br',
      });
    };
  
    return { showToast };
  });