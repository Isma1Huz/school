import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthLayout from '@/layouts/AuthLayout';
import { TextInput } from '@/components/forms/TextInput';
import { Button } from '@/components/ui/button';

export default function ForgotPassword() {
    const { data, setData, post, processing, errors, wasSuccessful } = useForm({ email: '' });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post('/forgot-password');
    }

    return (
        <AuthLayout
            title="Forgot your password?"
            subtitle="Enter your email and we will send you a reset link."
        >
            <Head title="Forgot Password" />
            {wasSuccessful ? (
                <p className="text-center text-sm text-green-700">
                    Password reset link sent! Please check your email.
                </p>
            ) : (
                <form onSubmit={handleSubmit} className="space-y-4">
                    <TextInput
                        label="Email Address"
                        type="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        error={errors.email}
                        required
                    />
                    <Button type="submit" className="w-full" disabled={processing}>
                        {processing ? 'Sending…' : 'Send Reset Link'}
                    </Button>
                </form>
            )}
        </AuthLayout>
    );
}
