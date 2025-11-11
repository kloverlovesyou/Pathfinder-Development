import { defineStore } from "pinia";
import { ref, reactive } from "vue";
import axios from "axios";

export const useRegistrationStore = defineStore("regStore", () => {
  const registeredPosts = reactive({});
  const myRegistrations = ref(new Set());

  // ✅ Registration button loading state
  const loading = ref({});
  function setLoading(trainingID, value) {
    loading.value = { ...loading.value, [trainingID]: value };
  }

  // ✅ Bookmark Data
  const bookmarkedTrainings = ref([]);
  const bookmarkLoading = reactive({});

  // ✅ Fetch user's registrations
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

  // ✅ Toggle Registration
  async function toggleRegister(training) {
    const trainingID =
      typeof training === "object"
        ? training.trainingID ?? training.TrainingID ?? training.id ?? training.ID
        : training;

    if (!trainingID) return console.error("Invalid training ID:", training);

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
        registeredPosts[trainingID] = {
          registrationID: res.data.data.registrationID,
        };
      }

      localStorage.setItem("registeredPosts", JSON.stringify(registeredPosts));
    } catch (err) {
      console.error("Failed to toggle registration:", err);
      throw err;
    } finally {
      setLoading(trainingID, false);
    }
  }

  // ✅ Fetch QR
  async function fetchTrainingQRCode(trainingID) {
    try {
      const token = localStorage.getItem("token");
      const res = await axios.get(
        import.meta.env.VITE_API_BASE_URL + `/trainings/${trainingID}`,
        { headers: { Authorization: `Bearer ${token}` } }
      );

      if (res.data.attendance_link) {
        if (!registeredPosts[trainingID]) registeredPosts[trainingID] = {};
        registeredPosts[trainingID].attendance_link = res.data.attendance_link;
        registeredPosts[trainingID].attendance_key = res.data.attendance_key;
      }
    } catch (err) {
      console.error("Failed to fetch QR:", err);
    }
  }

  // ✅ ✅ FETCH BOOKMARKS
  async function fetchBookmarks() {
    const token = localStorage.getItem("token");
    if (!token) return;

    try {
      const { data } = await axios.get(
        import.meta.env.VITE_API_BASE_URL + "/bookmarks",
        { headers: { Authorization: `Bearer ${token}` } }
      );
      bookmarkedTrainings.value = data; // array of trainingIDs
    } catch (err) {
      console.error("Failed to fetch bookmarks:", err);
    }
  }

  // ✅ ✅ TOGGLE BOOKMARK
  async function toggleBookmark(post) {
  const postID = post.TrainingID ?? post.CareerID ?? post.ID ?? post.id;
  if (!postID) return { success: false, error: "Invalid post ID" };

  if (bookmarkLoading[postID]) return { success: false, error: "Already loading" };
  bookmarkLoading[postID] = true;

  try {
    const token = localStorage.getItem("token");
    if (!token) return { success: false, error: "Not logged in" };

    const isBookmarked = bookmarkedTrainings.value.includes(postID);

    if (isBookmarked) {
      await axios.delete(`${import.meta.env.VITE_API_BASE_URL}/bookmarks/${postID}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      bookmarkedTrainings.value = bookmarkedTrainings.value.filter((id) => id !== postID);
    } else {
      await axios.post(
        `${import.meta.env.VITE_API_BASE_URL}/bookmarks`,
        { postID },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      bookmarkedTrainings.value.push(postID);
    }

    return { success: true, bookmarked: !isBookmarked };
  } catch (err) {
    console.error(err);
    return { success: false, error: err };
  } finally {
    bookmarkLoading[postID] = false;
  }
}

  function isTrainingBookmarked(trainingID) {
    return bookmarkedTrainings.value.includes(trainingID);
  }

  return {
    registeredPosts,
    myRegistrations,
    loading,
    fetchMyRegistrations,
    toggleRegister,
    fetchTrainingQRCode,

    // ✅ bookmark exports
    bookmarkedTrainings,
    bookmarkLoading,
    fetchBookmarks,
    toggleBookmark,
    isTrainingBookmarked,
  };
});