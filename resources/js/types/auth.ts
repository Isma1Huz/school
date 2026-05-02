import type { AuthUser } from './user';

export interface Auth {
    user: AuthUser | null;
}
