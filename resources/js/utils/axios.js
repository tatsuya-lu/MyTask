import axios from 'axios'

const api = axios.create({
    baseURL: '/api',
    withCredentials: true,
    headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
})

const setupAxios = async () => {
    try {
        await axios.get('/sanctum/csrf-cookie')
        
        api.interceptors.request.use(config => {
            const token = document.querySelector('meta[name="csrf-token"]').content
            if (token) {
                config.headers['X-CSRF-TOKEN'] = token
            }
            return config
        })

        api.interceptors.response.use(
            response => response,
            error => {
                if (error.response?.status === 419) {
                    // CSRFトークンが無効になった場合の処理
                    window.location.reload()
                }
                return Promise.reject(error)
            }
        )
    } catch (error) {
        console.error('CSRF token setup failed:', error)
    }
}

export { api, setupAxios }