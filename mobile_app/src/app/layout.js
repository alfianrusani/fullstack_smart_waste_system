import { Stack } from 'expo-router';

export default function RootLayout() {
  return (
    <Stack screenOptions={{ headerShown: false }}>
      <Stack.Screen name="home" />
      <Stack.Screen name="create" />
      <Stack.Screen name="report" />
      <Stack.Screen name="settings" />
    </Stack>
  );
}