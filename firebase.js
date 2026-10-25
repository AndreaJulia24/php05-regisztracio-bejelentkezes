import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
import { 
  getAuth, 
  createUserWithEmailAndPassword, 
  signInWithEmailAndPassword, 
  signOut, 
  onAuthStateChanged, 
  updateProfile 
} from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";
import { 
  getFirestore, 
  doc, 
  setDoc, 
  getDoc, 
  serverTimestamp 
} from "https://www.gstatic.com/firebasejs/10.8.0/firebase-firestore.js";

const firebaseConfig = {
  apiKey: "AIzaSyAuRorATRw0YeNf93DW24a-DNoEGPHFp4g",
  authDomain: "php05-register-login.firebaseapp.com",
  projectId: "php05-register-login",
  storageBucket: "php05-register-login.firebasestorage.app",
  messagingSenderId: "272354945817",
  appId: "1:272354945817:web:cd1c798d1bf3cb2a78b388",
  measurementId: "G-RR1P0SHJHK"
};
const app = initializeApp(firebaseConfig);
export const auth = getAuth(app);
export const db = getFirestore(app);

export async function regisztral(nev, email, jelszo) {
  const { user } = await createUserWithEmailAndPassword(auth, email, jelszo);
  await updateProfile(user, { displayName: nev });
  await setDoc(doc(db, 'profilok', user.uid), { 
    nev, 
    email, 
    letrehozva: serverTimestamp() 
  });
}

export async function belep(email, jelszo) {
  try {
    await signInWithEmailAndPassword(auth, email, jelszo);
  } catch (err) {
    console.error('Hiba a bejelentkezés során:', err);
    throw new Error(err.code === 'auth/too-many-requests'
      ? 'Túl sok próbálkozás, próbáld később.' 
      : 'Hibás e-mail vagy jelszó.');
  }
}

export const kilep = () => signOut(auth);