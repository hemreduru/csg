<?php

return [
    'title' => 'Profile',
    'sections' => [
        'account' => [
            'title' => 'Profile Information',
            'description' => 'Update your account\'s profile information and email address.',
        ],
        'password' => [
            'title' => 'Update Password',
            'description' => 'Use a long, random password to stay secure.',
        ],
        'delete' => [
            'title' => 'Delete Account',
            'description' => 'Once your account is deleted, all of its resources and data will be permanently deleted.',
            'confirm_title' => 'Are you sure you want to delete your account?',
            'confirm_text' => 'Please enter your password to confirm you would like to permanently delete your account.',
        ],
    ],
    'actions' => [
        'save' => 'Save Changes',
        'update_password' => 'Update Password',
        'delete' => 'Delete Account',
        'resend_verification' => 'Resend verification email',
    ],
    'fields' => [
        'current_password' => 'Current Password',
        'new_password' => 'New Password',
    ],
    'messages' => [
        'updated' => 'Profile updated.',
        'update_failed' => 'Profile update failed. Please try again.',
        'password_updated' => 'Password updated.',
        'password_update_failed' => 'Password update failed. Please try again.',
        'delete_failed' => 'Account deletion failed. Please try again.',
        'verification_sent' => 'A new verification link has been sent to your email address.',
        'email_unverified' => 'Your email address is unverified.',
    ],
];
