import axios from "axios";

const API_URL = "http://localhost/apk/server/api.php";

const api = axios.create({
  baseURL: API_URL,
  headers: { "Content-Type": "application/json" },
});

// 🧠 Interceptor untuk otomatis menambahkan token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

/* =============================
   🔹 LOGIN & REGISTER
============================= */
export const login = async (username, password) => {
  try {
    const res = await axios.post(`${API_URL}?action=login`, { username, password });
    if (res.data.token) {
      localStorage.setItem("token", res.data.token);
    }
    return res.data;
  } catch (err) {
    console.error("Login error:", err.response?.data || err.message);
    throw err;
  }
};

export const register = async (username, password) => {``
  try {
    const res = await axios.post(`${API_URL}?action=register`, { username, password });
    return res.data;
  } catch (err) {
    console.error("Register error:", err.response?.data || err.message);
    throw err;
  }
};

/* =============================
   🔹 LOGOUT
============================= */
export const logout = () => {
  localStorage.removeItem("token");
};

/* =============================
   🔹 CRUD OPERATIONS
============================= */
export const getUsers = async () => {
  const res = await api.get("");
  return res.data;
};

export const addUser = async (data) => {
  const res = await api.post("", data);
  return res.data;
};

export const updateUser = async (id, data) => {
  const res = await api.put(`?id_api=${id}`, data);
  return res.data;
};

export const deleteUser = async (id) => {
  const res = await api.delete(`?id_api=${id}`);
  return res.data;
};
