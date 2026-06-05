import axios from "axios";

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  headers: {
    Accept: "application/json",
  },
});

const activeControllers = new Set();
let authFailureHandler = null;

export const configureAuthFailureHandler = (handler) => {
  authFailureHandler = handler;
};

export const clearApiAuthHeader = () => {
  delete api.defaults.headers.common.Authorization;
};

export const abortActiveRequests = () => {
  activeControllers.forEach((controller) => controller.abort());
  activeControllers.clear();
};

export const isCanceledRequest = (error) => {
  return axios.isCancel(error) || error?.code === "ERR_CANCELED";
};

api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");

  if (token) {
    api.defaults.headers.common.Authorization = `Bearer ${token}`;
    config.headers.Authorization = `Bearer ${token}`;
  } else if (!config.headers.Authorization) {
    clearApiAuthHeader();
    delete config.headers.Authorization;
  }

  if (!config.skipGlobalAbort && !config.signal) {
    const controller = new AbortController();
    config.signal = controller.signal;
    config.__controller = controller;
    activeControllers.add(controller);
  }

  return config;
});

api.interceptors.response.use(
  (response) => {
    if (response.config.__controller) {
      activeControllers.delete(response.config.__controller);
    }

    return response;
  },
  (error) => {
    if (error.config?.__controller) {
      activeControllers.delete(error.config.__controller);
    }

    const status = error.response?.status;
    const isCanceled = isCanceledRequest(error);

    if (!isCanceled && !error.config?.skipAuthRedirect && [401, 403].includes(status)) {
      authFailureHandler?.();
    }

    return Promise.reject(error);
  }
);

export default api;
