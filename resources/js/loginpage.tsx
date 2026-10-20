import React, { useState, FormEvent } from 'react';
import { createRoot } from 'react-dom/client';
import { Atom, Mail, Lock, Eye, EyeOff, LogIn, AlertCircle, CheckCircle2, Loader2 } from 'lucide-react';

declare global {
  interface Window {
    laravelApiUrl?: string;
    csrfToken?: string;
    loginAction?: string;
    loginErrors?: string[];
    loginOldEmail?: string;
    loginOldRemember?: boolean;
    loginSuccess?: string;
  }
}

const API_BASE = window.laravelApiUrl || 'http://localhost/Chemical';

// Break out of the menu iframe if a page inside it redirected here after the session expired
if (window.top && window.top !== window.self) {
  window.top.location.href = window.location.href;
}

// text-base (16px) on inputs stops iOS Safari zooming in when a field is focused
const inputCls =
  'w-full h-12 rounded-lg bg-white border border-slate-300 text-base text-slate-900 placeholder-slate-400 ' +
  'outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/25 transition';

const LoginPage: React.FC = () => {
  const [email, setEmail] = useState(window.loginOldEmail || '');
  const [password, setPassword] = useState('');
  const [remember, setRemember] = useState(!!window.loginOldRemember);
  const [showPw, setShowPw] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [clientError, setClientError] = useState('');

  // Fields start read-only so the browser can't auto-fill them on page load.
  // The first tap/click/tab unlocks the field; the browser then shows its saved-user
  // list and only fills when a user is picked.
  const [unlocked, setUnlocked] = useState({ email: false, password: false });
  const unlock = (field: 'email' | 'password') =>
    setUnlocked((u) => (u[field] ? u : { ...u, [field]: true }));

  const serverError = window.loginErrors && window.loginErrors.length ? window.loginErrors[0] : '';
  const success = window.loginSuccess || '';
  const error = clientError || serverError;

  // Native form POST: password never goes in a URL, and Laravel's session/redirect flow works as normal
  const handleSubmit = (e: FormEvent<HTMLFormElement>) => {
    if (!email.trim() || !password) {
      e.preventDefault();
      setClientError('Enter your email and password.');
      return;
    }
    setClientError('');
    setSubmitting(true);
  };

  return (
    <div
      className="min-h-[100dvh] flex flex-col bg-slate-100 text-slate-900"
      style={{ paddingBottom: 'env(safe-area-inset-bottom)' }}
    >
      {/* ── Top bar: Buzz Chem on the far left ─────────────────────────── */}
      <header
        className="w-full bg-white border-b border-slate-200 shadow-sm"
        style={{ paddingTop: 'env(safe-area-inset-top)' }}
      >
        <div className="h-14 px-4 sm:px-6 flex items-center">
          <div className="flex items-center gap-2.5">
            <div className="h-9 w-9 rounded-lg bg-indigo-500 flex items-center justify-center shrink-0">
              <Atom className="h-6 w-6 text-white" strokeWidth={2.25} />
            </div>
            <span className="text-lg font-bold tracking-tight text-slate-900">Buzz Chem</span>
          </div>
        </div>
      </header>

      {/* ── Login card ─────────────────────────────────────────────────── */}
      <main className="flex-1 flex items-start sm:items-center justify-center px-4 py-8 sm:py-12">
        <div className="w-full max-w-sm bg-white rounded-xl border border-slate-200 shadow-md overflow-hidden">
          <div className="h-1 bg-sky-500" />

          <div className="px-5 sm:px-6 pt-6 pb-6">
            <h1 className="text-xl font-bold text-slate-900 mb-1">Sign in</h1>
            <p className="text-slate-500 text-sm mb-5">Use your Buzz Chem account to continue.</p>

            {success && !error && (
              <div className="flex items-start gap-2 mb-4 rounded-lg border border-sky-200 bg-sky-50 px-3 py-3 text-sm text-sky-800">
                <CheckCircle2 className="h-4 w-4 mt-0.5 shrink-0" />
                <span>{success}</span>
              </div>
            )}

            {error && (
              <div role="alert" className="flex items-start gap-2 mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-3 text-sm text-red-700">
                <AlertCircle className="h-4 w-4 mt-0.5 shrink-0" />
                <span>{error}</span>
              </div>
            )}

            <form method="POST" action={window.loginAction || `${API_BASE}/custom-login`} onSubmit={handleSubmit} noValidate>
              <input type="hidden" name="_token" value={window.csrfToken || ''} />

              <label htmlFor="email" className="block text-xs font-bold text-slate-600 mb-1.5">Email</label>
              <div className="relative mb-4">
                <Mail className="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" />
                <input
                  id="email"
                  name="email"
                  type="email"
                  inputMode="email"
                  autoComplete="username"
                  autoCapitalize="none"
                  autoCorrect="off"
                  spellCheck={false}
                  enterKeyHint="next"
                  readOnly={!unlocked.email}
                  onPointerDown={() => unlock('email')}
                  onFocus={() => unlock('email')}
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className={`${inputCls} pl-11 pr-3`}
                  placeholder="you@buzzchem.co.za"
                />
              </div>

              <label htmlFor="password" className="block text-xs font-bold text-slate-600 mb-1.5">Password</label>
              <div className="relative mb-4">
                <Lock className="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" />
                <input
                  id="password"
                  name="password"
                  type={showPw ? 'text' : 'password'}
                  autoComplete="current-password"
                  autoCapitalize="none"
                  autoCorrect="off"
                  enterKeyHint="go"
                  readOnly={!unlocked.password}
                  onPointerDown={() => unlock('password')}
                  onFocus={() => unlock('password')}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className={`${inputCls} pl-11 pr-12`}
                  placeholder="••••••••"
                />
                <button
                  type="button"
                  onClick={() => setShowPw((s) => !s)}
                  className="absolute right-0 top-0 h-12 w-12 flex items-center justify-center text-slate-400 hover:text-slate-700"
                  aria-label={showPw ? 'Hide password' : 'Show password'}
                >
                  {showPw ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
                </button>
              </div>

              {/* Whole row is tappable, min 44px tall */}
              <label className="flex items-center gap-3 min-h-[44px] text-base sm:text-sm text-slate-700 mb-4 cursor-pointer select-none">
                <input
                  type="checkbox"
                  name="remember"
                  value="1"
                  checked={remember}
                  onChange={(e) => setRemember(e.target.checked)}
                  className="h-5 w-5 accent-sky-500"
                />
                Remember me
              </label>

              <button
                type="submit"
                disabled={submitting}
                className="w-full h-12 flex items-center justify-center gap-2 rounded-lg bg-sky-500 hover:bg-sky-600 active:bg-sky-700 disabled:bg-slate-300 disabled:cursor-wait text-white font-bold text-base transition-colors"
              >
                {submitting ? (
                  <><Loader2 className="h-5 w-5 animate-spin" /> Signing in…</>
                ) : (
                  <><LogIn className="h-5 w-5" /> Sign in</>
                )}
              </button>
            </form>
          </div>
        </div>
      </main>

      <footer className="py-4 text-center text-xs text-slate-400">
        © {new Date().getFullYear()} Buzz Chem
      </footer>
    </div>
  );
};

export default LoginPage;

const rootEl = document.getElementById('root');
if (rootEl) {
  createRoot(rootEl).render(<LoginPage />);
}