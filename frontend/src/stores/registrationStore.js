import { defineStore } from "pinia";
import { ref, reactive } from "vue";
import axios from "axios";

export const useRegistrationStore = defineStore("regStore", () => {
  const registeredPosts = reactive({});
  const myRegistrations = ref(new Set());
  const loading = ref({}); // 🔹 use ref instead of reactive

  function setLoading(trainingID, value) {
    loading.value = { ...loading.value, [trainingID]: value }; // triggers reactivity
  }

  // Fetch user's registrations
  async function fetchMyRegistrations() {
    const token = localStorage.getItem("token");
    if (!token) return;

    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/registrations",
      { headers: { Authorization: `Bearer ${token}` } }
    );

    myRegistrations.value.clear();
    Object.keys(registeredPosts).forEach((k) => delete registeredPosts[k]);

    res.data.forEach((r) => {
      myRegistrations.value.add(r.trainingID);
      registeredPosts[r.trainingID] = { registrationID: r.registrationID };
    });

    localStorage.setItem("registeredPosts", JSON.stringify(registeredPosts));
  }

    // Toggle registration with loading state
    async function toggleRegister(training) {
    // Resolve ID
    const trainingID =
        typeof training === "object"
        ? training.trainingID ?? training.TrainingID ?? training.id ?? training.ID
        : training;

    if (!trainingID) {
        console.error("Invalid training ID passed:", training);
        return;
    }

    const token = localStorage.getItem("token");
    if (!token) return alert("Please log in first");

    setLoading(trainingID, true);

    try {
        if (myRegistrations.value.has(trainingID)) {
        // UNREGISTER
        await axios.delete(
            import.meta.env.VITE_API_BASE_URL + `/registrations/${trainingID}`,
            { headers: { Authorization: `Bearer ${token}` } }
        );
        myRegistrations.value.delete(trainingID);
        delete registeredPosts[trainingID];
        } else {
        // REGISTER
        const res = await axios.post(
            import.meta.env.VITE_API_BASE_URL + `/registrations`,
            { trainingID },
            { headers: { Authorization: `Bearer ${token}` } }
        );
        myRegistrations.value.add(trainingID);
        registeredPosts[trainingID] = { registrationID: res.data.data.registrationID };
        }

        localStorage.setItem("registeredPosts", JSON.stringify(registeredPosts));
    } catch (err) {
        console.error("Failed to toggle registration:", err);
        throw err;
    } finally {
        setLoading(trainingID, false);
    }
    }

    return {
        registeredPosts,
        myRegistrations,
        loading, // 🔹 now reactive
        fetchMyRegistrations,
        toggleRegister,
    };
});