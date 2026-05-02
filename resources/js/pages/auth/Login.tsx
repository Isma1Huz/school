import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { GraduationCap } from 'lucide-react';
import { Button } from '@/components/ui/button';

export default function Login() {
  const { data, setData, post, processing, errors } = useForm({
    email: '',
    password: '',
    remember: false,
  });

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    post('/login');
  }

  return (
    <div className="flex min-h-screen bg-gradient-to-br from-slate-900 via-[#1e293b] to-slate-800">
      <Head title="Sign In" />

      {/* Branding panel (hidden on mobile) */}
      <div className="hidden flex-1 items-center justify-center lg:flex">
        <div className="text-center">
          <div className="mx-auto flex h-24 w-24 items-center justify-center rounded-2xl bg-[#800020] shadow-2xl">
            <GraduationCap className="h-12 w-12 text-white" />
          </div>
          <h1 className="mt-6 text-4xl font-bold text-white">Schoolzee</h1>
          <p className="mt-2 text-lg text-slate-300">Multitenant School Management System</p>
          <p className="mt-6 max-w-sm text-sm text-slate-400">
            Manage your educational institution with ease — from academics to finance, all in one place.
          </p>
        </div>
      </div>

      {/* Form panel */}
      <div className="flex w-full items-center justify-center p-6 lg:w-[480px] lg:border-l lg:border-white/10">
        <div className="w-full max-w-md">
          {/* Mobile logo */}
          <div className="mb-8 flex items-center gap-3 lg:hidden">
            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#800020]">
              <GraduationCap className="h-6 w-6 text-white" />
            </div>
            <span className="text-xl font-bold text-white">Schoolzee</span>
          </div>

          <div className="rounded-2xl border border-white/10 bg-white/5 p-8 backdrop-blur-sm">
            <div className="mb-6">
              <h2 className="text-2xl font-bold text-white">Sign in</h2>
              <p className="mt-1 text-sm text-slate-400">Welcome back! Please enter your credentials.</p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="mb-1.5 block text-sm font-medium text-slate-300">Email address</label>
                <input
                  type="email"
                  value={data.email}
                  onChange={(e) => setData('email', e.target.value)}
                  autoComplete="email"
                  required
                  className="w-full rounded-lg border border-white/20 bg-white/10 px-4 py-2.5 text-sm text-white placeholder-slate-400 focus:border-[#800020] focus:outline-none focus:ring-1 focus:ring-[#800020]"
                  placeholder="you@example.com"
                />
                {errors.email && <p className="mt-1 text-xs text-red-400">{errors.email}</p>}
              </div>

              <div>
                <label className="mb-1.5 block text-sm font-medium text-slate-300">Password</label>
                <input
                  type="password"
                  value={data.password}
                  onChange={(e) => setData('password', e.target.value)}
                  autoComplete="current-password"
                  required
                  className="w-full rounded-lg border border-white/20 bg-white/10 px-4 py-2.5 text-sm text-white placeholder-slate-400 focus:border-[#800020] focus:outline-none focus:ring-1 focus:ring-[#800020]"
                  placeholder="••••••••"
                />
                {errors.password && <p className="mt-1 text-xs text-red-400">{errors.password}</p>}
              </div>

              <div className="flex items-center justify-between">
                <label className="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={data.remember}
                    onChange={(e) => setData('remember', e.target.checked)}
                    className="h-4 w-4 rounded border-white/20 bg-white/10"
                  />
                  Remember me
                </label>
                <Link href="/forgot-password" className="text-sm text-slate-300 hover:text-white hover:underline">
                  Forgot password?
                </Link>
              </div>

              <Button
                type="submit"
                disabled={processing}
                className="w-full bg-[#800020] hover:bg-[#6b001b] py-2.5 text-sm font-semibold text-white"
              >
                {processing ? 'Signing in…' : 'Sign In'}
              </Button>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}
