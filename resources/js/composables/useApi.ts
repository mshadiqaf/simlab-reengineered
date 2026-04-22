import { ref  } from 'vue';
import type {Ref} from 'vue';

interface ApiOptions extends RequestInit {
    // Additional options can be added here
}

export function useApi<T = any>(baseUrl: string = '/api') {
    const data: Ref<T | null> = ref(null);
    const error: Ref<Error | null> = ref(null);
    const loading: Ref<boolean> = ref(false);

    const execute = async (endpoint: string, options: ApiOptions = {}): Promise<T | null> => {
        loading.value = true;
        error.value = null;

        try {
            // Read CSRF token from meta tag if available
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            const headers = new Headers(options.headers);
            headers.set('Accept', 'application/json');
            
            // Only set Content-Type to application/json if not sending FormData
            if (!(options.body instanceof FormData)) {
                headers.set('Content-Type', 'application/json');
            }

            if (csrfToken) {
                headers.set('X-CSRF-TOKEN', csrfToken);
            }

            const response = await fetch(`${baseUrl}${endpoint.startsWith('/') ? endpoint : `/${endpoint}`}`, {
                ...options,
                headers,
                // Ensure cookies (like Sanctum/Session) are sent
                credentials: 'same-origin',
            });

            if (!response.ok) {
                let errorMsg = response.statusText;

                try {
                    const errorData = await response.json();
                    errorMsg = errorData.message || errorMsg;
                } catch {
                    // response is not json
                }

                throw new Error(errorMsg || `API Request failed with status ${response.status}`);
            }

            const result = await response.json();
            data.value = result.data !== undefined ? result.data : result;

            return data.value;
        } catch (e: any) {
            error.value = e;

            return null;
        } finally {
            loading.value = false;
        }
    };

    return {
        data,
        error,
        loading,
        execute
    };
}
