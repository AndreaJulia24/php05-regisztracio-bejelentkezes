import { initializeApp } from 'firebase/app';
import { getAuth, createUserWithEmailAndPassword, signInWithEmailAndPassword,
         signOut, onAuthStateChanged, updateProfile } from 'firebase/auth';
import { getFirestore, doc, setDoc, getDoc, serverTimestamp } from 'firebase/firestore';

const app = initializeApp({ apiKey: '...', authDomain: 'umfst-portal.firebaseapp.com', projectId: 'umfst-portal' });
const auth = getAuth(app);
const db = getFirestore(app);

export async function regisztral(nev, email, jelszo) {
  // a jelszó hash-elését, a sózást és a brute-force védelmet a Firebase Auth végzi
  const { user } = await createUserWithEmailAndPassword(auth, email, jelszo);
  await updateProfile(user, { displayName: nev });
  await setDoc(doc(db, 'profilok', user.uid), { nev, email, letrehozva: serverTimestamp() });
}

export async function belep(email, jelszo) {
  try {
    await signInWithEmailAndPassword(auth, email, jelszo);
  } catch (err) {
    // auth/invalid-credential, auth/too-many-requests – általános üzenet a felhasználónak
    throw new Error(err.code === 'auth/too-many-requests'
      ? 'Túl sok próbálkozás, próbáld később.' : 'Hibás e-mail vagy jelszó.');
  }
}

export const kilep = () => signOut(auth);

// „védett oldal”: a UI az auth állapotra reagál; a valódi védelem a rules!
onAuthStateChanged(auth, async (user) => {
  if (!user) { location.hash = '#/belepes'; return; }
  const p = await getDoc(doc(db, 'profilok', user.uid));
  document.querySelector('#udv').textContent = 'Szia, ' + (p.data()?.nev ?? user.email) + '!';
});