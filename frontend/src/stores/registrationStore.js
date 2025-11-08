import { defineStore } from "pinia";
import { ref, reactive } from "vue";
import axios from "axios";

export const useRegistrationStore = defineStore("regStore", () => {
  const registeredPosts = reactive({});
  const myRegistrations = ref(new Set());

  async function fetchMyRegistrations() {
    const token = localStorage.getItem("token");
    if (!token) return;

    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/registrations",
      { headers: { Authorization: `Bearer ${token}` } }
    );

    myRegistrations.value.clear();
    res.data.forEach((r) => {
      myRegistrations.value.add(r.trainingID);
      registeredPosts[r.trainingID] = {
        registrationID: r.registrationID,
      };
    });

    localStorage.setItem("registeredPosts", JSON.stringify(registeredPosts));
  }

  async function toggleRegister(trainingID) {
    const token = localStorage.getItem("token");
    if (!token) return alert("Please log in first");

    if (myRegistrations.value.has(trainingID)) {
      await axios.delete(
        import.meta.env.VITE_API_BASE_URL + `/registrations/${trainingID}`,
        { headers: { Authorization: `Bearer ${token}` } }
      );

      myRegistrations.value.delete(trainingID);
      delete registeredPosts[trainingID];

    } else {
      const res = await axios.post(
        import.meta.env.VITE_API_BASE_URL + `/registrations`,
        { trainingID },
        { headers: { Authorization: `Bearer ${token}` } }
      );

      myRegistrations.value.add(trainingID);
      registeredPosts[trainingID] = {
        registrationID: res.data.data.registrationID,
      };
    }

    localStorage.setItem("registeredPosts", JSON.stringify(registeredPosts));
  }

  return {
    registeredPosts,
    myRegistrations,
    fetchMyRegistrations,
    toggleRegister,
  };
});