import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthLayout from '@/layouts/AuthLayout';
import { Button } from '@/components/ui/button';

/**
 * Inertia page rendered by Fortify for the two-factor authentication challenge.
 * After login, users with 2FA enabled are redirected here.
 * They can confirm with a TOTP code or a recovery code.
 */
export default function TwoFactorChallenge() {
    const [useRecoveryCode, setUseRecoveryCode] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        code: '',
        recovery_code: '',
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post('/two-factor-challenge');
    }

    return (
        <AuthLayout
            title="Two-Factor Confirmation"
            subtitle={
                useRecoveryCode
                    ? 'Enter one of your emergency recovery codes.'
                    : 'Enter the 6-digit code from your authenticator app.'
            }
        >
            <Head title="Two-Factor Authentication" />

            <form onSubmit={handleSubmit} className="space-y-4">
                {!useRecoveryCode ? (
                    <div>
                        <label className="mb-1.5 block text-sm font-medium text-gray-700">
                            Authentication Code
                        </label>
                        <input
                            type="text"
                            inputMode="numeric"
                            autoComplete="one-time-code"
                            value={data.code}
                            onChange={(e) => setData('code', e.target.value)}
                            maxLength={6}
                            placeholder="123456"
                            autoFocus
                            className="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-center text-lg tracking-widest focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        />
                        {errors.code && (
                            <p className="mt-1 text-xs text-red-600">{errors.code}</p>
                        )}
                    </div>
                ) : (
                    <div>
                        <label className="mb-1.5 block text-sm font-medium text-gray-700">
                            Recovery Code
                        </label>
                        <input
                            type="text"
                            autoComplete="off"
                            value={data.recovery_code}
                            onChange={(e) => setData('recovery_code', e.target.value)}
                            placeholder="xxxx-xxxx"
                            autoFocus
                            className="w-full rounded-lg border border-gray-300 px-4 py-2.5 font-mono text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        />
                        {errors.recovery_code && (
                            <p className="mt-1 text-xs text-red-600">{errors.recovery_code}</p>
                        )}
                    </div>
                )}

                <Button type="submit" className="w-full" disabled={processing}>
                    {processing ? 'Verifying…' : 'Confirm'}
                </Button>

                <button
                    type="button"
                    onClick={() => {
                        setUseRecoveryCode((v) => !v);
                        setData({ code: '', recovery_code: '' });
                    }}
                    className="w-full text-center text-sm text-gray-500 underline hover:text-gray-700"
                >
                    {useRecoveryCode
                        ? 'Use authentication code instead'
                        : 'Use a recovery code instead'}
                </button>
            </form>
        </AuthLayout>
    );
}
