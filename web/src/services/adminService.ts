import type { AdminUser } from '@/types';
import { http } from './apiClient';

export async function listUsers(): Promise<AdminUser[]> {
  const { data } = await http.get<{ data: AdminUser[] }>('/admin/users');
  return data.data;
}

export async function setAdmin(userId: number, isAdmin: boolean): Promise<AdminUser> {
  const url = `/admin/users/${userId}/admin`;
  const { data } = isAdmin ? await http.put<AdminUser>(url) : await http.delete<AdminUser>(url);
  return data;
}
