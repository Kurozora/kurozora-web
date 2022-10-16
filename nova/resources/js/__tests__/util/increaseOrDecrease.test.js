import increaseOrDecrease from '@/util/increaseOrDecrease'

test('it can calculate increase in percentage', () => {
  expect(increaseOrDecrease(50, 0)).toBe(null)
  expect(increaseOrDecrease(45, 10)).toBe(350)
  expect(increaseOrDecrease(45, 36)).toBe(25)
  expect(increaseOrDecrease(45, 40)).toBe(12.5)
  expect(increaseOrDecrease(50, -50)).toBe(200)
})

test('it can calculate decrease in percentage', () => {
  expect(increaseOrDecrease(0, 50)).toBe(-100)
  expect(increaseOrDecrease(10, 45)).toBe(-77.77777777777779)
  expect(increaseOrDecrease(36, 45)).toBe(-20)
  expect(increaseOrDecrease(40, 45)).toBe(-11.11111111111111)
  expect(increaseOrDecrease(-50, 50)).toBe(-200)
})
