#include <stdio.h>

FILE *fin, *fout;

void give_score(int s)
{
    printf("%d", s);
    exit(0);
}

int main()
{
    int a, b, sum;

    fin = fopen("adunare.in", "r");
    fout = fopen("adunare.out", "r");

    if (!fin || !fout) {
	fprintf(stderr, "Failed fopen. Missing files?");
	give_score(0);
    }

    fscanf(fin, "%d %d", &a, &b);
    fscanf(fout, "%d", &sum);

    if (sum == (a + b)) {
	fprintf(stderr, "Okay!");
	give_score(10);
    } else {
	fprintf(stderr, "%d != %d", a - b, sum);
	give_score(0);
    }

    return 0;
}
