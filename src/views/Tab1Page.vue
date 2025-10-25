<template>
  <ion-page>
    <ion-header>
      <ion-toolbar color="primary">
        <ion-title>Login, Register & CRUD</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content class="ion-padding">
      <!-- ================= LOGIN FORM ================= -->
      <div v-if="!isLoggedIn && !showRegister">
        <ion-input
          v-model="loginForm.username"
          label="Username"
          placeholder="Masukkan username"
          fill="outline"
        ></ion-input>

        <ion-input
          v-model="loginForm.password"
          label="Password"
          type="password"
          placeholder="Masukkan password"
          fill="outline"
        ></ion-input>

        <ion-button expand="block" color="primary" @click="handleLogin">
          Login
        </ion-button>

        <ion-button expand="block" fill="clear" @click="showRegister = true">
          Belum punya akun? Daftar di sini
        </ion-button>
      </div>

      <!-- ================= REGISTER FORM ================= -->
      <div v-if="showRegister && !isLoggedIn">
        <ion-input
          v-model="registerForm.username"
          label="Username"
          placeholder="Masukkan username baru"
          fill="outline"
        ></ion-input>

        <ion-input
          v-model="registerForm.password"
          label="Password"
          type="password"
          placeholder="Masukkan password"
          fill="outline"
        ></ion-input>

        <ion-input
          v-model="registerForm.confirmPassword"
          label="Konfirmasi Password"
          type="password"
          placeholder="Ulangi password"
          fill="outline"
        ></ion-input>

        <ion-button expand="block" color="success" @click="handleRegister">
          Daftar Akun
        </ion-button>

        <ion-button expand="block" fill="clear" @click="showRegister = false">
          Sudah punya akun? Login
        </ion-button>
      </div>

      <!-- ================= CRUD SECTION ================= -->
      <div v-if="isLoggedIn">
        <ion-button expand="block" color="danger" @click="handleLogout">
          Logout
        </ion-button>

        <ion-input
          placeholder="Judul"
          v-model="form.title"
          fill="outline"
          label="Title"
        ></ion-input>

        <ion-input
          placeholder="Deskripsi"
          v-model="form.deskripsi"
          fill="outline"
          label="Deskripsi"
        ></ion-input>

        <ion-button
          expand="block"
          color="success"
          @click="isEditing ? handleUpdate() : handleAdd()"
        >
          {{ isEditing ? 'Update Data' : 'Tambah Data' }}
        </ion-button>

        <ion-list>
          <ion-item v-for="user in users" :key="user.id_api">
            <ion-label>
              <h2>{{ user.title }}</h2>
              <p>{{ user.deskripsi }}</p>
            </ion-label>

            <ion-button color="warning" @click="handleEdit(user)">Edit</ion-button>
            <ion-button color="danger" @click="handleDelete(user.id_api)">Hapus</ion-button>
          </ion-item>
        </ion-list>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup>
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent,
  IonButton, IonInput, IonList, IonItem, IonLabel
} from '@ionic/vue';
import { ref, onMounted } from 'vue';
import { login, register, getUsers, addUser, deleteUser, updateUser, logout } from '../api';

// === STATE ===
const isLoggedIn = ref(false);
const showRegister = ref(false);
const loginForm = ref({ username: '', password: '' });
const registerForm = ref({ username: '', password: '', confirmPassword: '' });
const users = ref([]);
const form = ref({ title: '', deskripsi: '' });
const isEditing = ref(false);
const editId = ref(null);

// === CEK LOGIN SAAT LOAD ===
onMounted(() => {
  const token = localStorage.getItem('token');
  if (token) {
    isLoggedIn.value = true;
    fetchData();
  }
});

// === LOGIN ===
const handleLogin = async () => {
  try {
    const res = await login(loginForm.value.username, loginForm.value.password);
    if (res.success && res.token) {
      localStorage.setItem('token', res.token);
      isLoggedIn.value = true;
      fetchData();
    } else {
      alert(res.message || 'Username atau password salah');
    }
  } catch (err) {
    alert('Gagal login: ' + err.message);
  }
};

// === REGISTER ===
const handleRegister = async () => {
  if (!registerForm.value.username || !registerForm.value.password) {
    alert('Username dan password wajib diisi!');
    return;
  }
  if (registerForm.value.password !== registerForm.value.confirmPassword) {
    alert('Password tidak sama!');
    return;
  }

  try {
    const res = await register(registerForm.value.username, registerForm.value.password);
    if (res.success) {
      alert('Registrasi berhasil! Silakan login.');
      showRegister.value = false;
      registerForm.value = { username: '', password: '', confirmPassword: '' };
    } else {
      alert(res.message || 'Gagal daftar.');
    }
  } catch (err) {
    alert('Gagal register: ' + err.message);
  }
};

// === LOGOUT ===
const handleLogout = () => {
  logout();
  isLoggedIn.value = false;
  users.value = [];
};

// === CRUD FUNCTIONS ===
const fetchData = async () => {
  try {
    users.value = await getUsers();
  } catch (err) {
    alert('Gagal ambil data, pastikan sudah login.');
  }
};

const handleAdd = async () => {
  if (!form.value.title || !form.value.deskripsi) return;
  await addUser(form.value);
  form.value = { title: '', deskripsi: '' };
  fetchData();
};

const handleEdit = (user) => {
  form.value.title = user.title;
  form.value.deskripsi = user.deskripsi;
  editId.value = user.id_api;
  isEditing.value = true;
};

const handleUpdate = async () => {
  await updateUser(editId.value, form.value);
  form.value = { title: '', deskripsi: '' };
  editId.value = null;
  isEditing.value = false;
  fetchData();
};

const handleDelete = async (id) => {
  await deleteUser(id);
  fetchData();
};
</script>

<style>
ion-input {
  margin-bottom: 10px;
}
ion-button {
  margin-bottom: 10px;
}
</style>
